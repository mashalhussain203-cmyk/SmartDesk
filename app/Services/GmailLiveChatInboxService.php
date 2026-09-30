<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GmailLiveChatInboxService
{
    private int $tagCounter = 0;

    public function __construct(
        private readonly LiveChatEmailService $email
    ) {
    }

    /**
     * Haalt ongelezen Gmail-replies op via IMAP en zet ze in live_chat_messages.
     * Er is geen ext-imap nodig; dit gebruikt een TLS socket.
     *
     * @return array{ok:bool,configured:bool,checked:int,inserted:int,duplicates:int,errors:int,message?:string}
     */
    public function sync(?int $requestedLimit = null): array
    {
        if (! config('live-chat-email.enabled', true)) {
            return [
                'ok' => true,
                'configured' => false,
                'checked' => 0,
                'inserted' => 0,
                'duplicates' => 0,
                'errors' => 0,
                'message' => 'E-mailhandoff is uitgeschakeld.',
            ];
        }

        $username = strtolower(trim((string) config('live-chat-email.gmail_username')));
        $password = preg_replace('/\s+/', '', (string) config('live-chat-email.gmail_app_password')) ?: '';

        if ($username === '' || $password === '') {
            return [
                'ok' => true,
                'configured' => false,
                'checked' => 0,
                'inserted' => 0,
                'duplicates' => 0,
                'errors' => 0,
                'message' => 'LIVE_CHAT_GMAIL_APP_PASSWORD ontbreekt nog.',
            ];
        }

        $limit = max(1, min(100, $requestedLimit ?: (int) config('live-chat-email.gmail_sync_limit', 25)));
        $socket = $this->connect();

        $checked = 0;
        $inserted = 0;
        $duplicates = 0;
        $errors = 0;

        try {
            $this->command($socket, 'LOGIN '.$this->quote($username).' '.$this->quote($password));
            $this->command($socket, 'SELECT INBOX');

            $usernameParts = explode('@', $username, 2);
            $aliasPrefix = count($usernameParts) === 2
                ? $usernameParts[0].'+lc-'
                : '+lc-';

            $search = $this->command(
                $socket,
                'UID SEARCH UNSEEN OR HEADER To '.$this->quote($aliasPrefix)
                    .' HEADER Delivered-To '.$this->quote($aliasPrefix)
            );
            $uids = $this->extractSearchUids($search['text']);
            $uids = array_slice($uids, -$limit);

            foreach ($uids as $uid) {
                $headerResponse = $this->command(
                    $socket,
                    'UID FETCH '.$uid.' (BODY.PEEK[HEADER.FIELDS (TO DELIVERED-TO X-ORIGINAL-TO FROM SUBJECT MESSAGE-ID IN-REPLY-TO CONTENT-TYPE)])'
                );

                $headersRaw = $headerResponse['literals'][0] ?? '';
                if ($headersRaw === '') {
                    continue;
                }

                $headers = $this->parseHeaders($headersRaw);
                $recipient = $this->firstRecipientHeader($headers);

                if (! $this->looksLikeLiveChatAlias($recipient, $username)) {
                    continue;
                }

                $checked++;

                try {
                    $fullResponse = $this->command($socket, 'UID FETCH '.$uid.' (BODY.PEEK[])');
                    $raw = $fullResponse['literals'][0] ?? '';

                    if ($raw === '') {
                        throw new RuntimeException('Gmail leverde geen berichtinhoud terug.');
                    }

                    $parsed = $this->parseMimeMessage($raw);
                    $result = $this->email->receiveGmailMessage([
                        'recipient' => $recipient,
                        'from' => $this->extractEmailAddress((string) ($headers['from'] ?? '')),
                        'message_id' => trim((string) ($headers['message-id'] ?? '')),
                        'in_reply_to' => trim((string) ($headers['in-reply-to'] ?? '')) ?: null,
                        'subject' => $this->decodeHeader((string) ($headers['subject'] ?? '')),
                        'body' => $this->cleanReplyText($parsed['text']),
                        'attachments' => $parsed['attachments'],
                    ]);

                    $inserted += (int) ($result['inserted'] ?? 0);
                    if (($result['duplicate'] ?? false) === true) {
                        $duplicates++;
                    }

                    $this->command($socket, 'UID STORE '.$uid.' +FLAGS.SILENT (\\Seen)');
                } catch (Throwable $exception) {
                    $errors++;
                    Log::warning('Live-chat Gmail reply kon niet worden verwerkt.', [
                        'uid' => $uid,
                        'exception' => $exception,
                    ]);
                }
            }

            return [
                'ok' => true,
                'configured' => true,
                'checked' => $checked,
                'inserted' => $inserted,
                'duplicates' => $duplicates,
                'errors' => $errors,
            ];
        } finally {
            try {
                $this->command($socket, 'LOGOUT', false);
            } catch (Throwable) {
                // verbinding wordt hieronder altijd gesloten
            }

            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    /** @return resource */
    private function connect()
    {
        $host = trim((string) config('live-chat-email.gmail_host', 'imap.gmail.com'));
        $port = (int) config('live-chat-email.gmail_port', 993);
        $timeout = max(5, (int) config('live-chat-email.gmail_timeout_seconds', 15));

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'SNI_enabled' => true,
            ],
        ]);

        $socket = @stream_socket_client(
            'ssl://'.$host.':'.$port,
            $errno,
            $error,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (! is_resource($socket)) {
            throw new RuntimeException('Kan Gmail IMAP niet bereiken: '.$error.' ('.$errno.').');
        }

        stream_set_timeout($socket, $timeout);
        $greeting = fgets($socket);

        if (! is_string($greeting) || ! str_starts_with($greeting, '* OK')) {
            fclose($socket);
            throw new RuntimeException('Gmail IMAP gaf geen geldige begroeting terug.');
        }

        return $socket;
    }

    /**
     * @param resource $socket
     * @return array{text:string,literals:array<int,string>}
     */
    private function command($socket, string $command, bool $mustSucceed = true): array
    {
        $tag = 'A'.str_pad((string) ++$this->tagCounter, 4, '0', STR_PAD_LEFT);
        fwrite($socket, $tag.' '.$command."\r\n");

        $text = '';
        $literals = [];
        $statusLine = '';

        while (! feof($socket)) {
            $line = fgets($socket);
            if ($line === false) {
                break;
            }

            $text .= $line;

            if (preg_match('/\{(\d+)\}\r?\n$/', $line, $matches) === 1) {
                $length = (int) $matches[1];
                $literal = $this->readBytes($socket, $length);
                $literals[] = $literal;
                $text .= $literal;
            }

            if (str_starts_with($line, $tag.' ')) {
                $statusLine = trim($line);
                break;
            }
        }

        if ($mustSucceed && ! str_starts_with($statusLine, $tag.' OK')) {
            throw new RuntimeException('Gmail IMAP opdracht mislukt: '.$statusLine);
        }

        return [
            'text' => $text,
            'literals' => $literals,
        ];
    }

    /** @param resource $socket */
    private function readBytes($socket, int $length): string
    {
        $buffer = '';

        while (strlen($buffer) < $length && ! feof($socket)) {
            $chunk = fread($socket, $length - strlen($buffer));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $buffer .= $chunk;
        }

        return $buffer;
    }

    private function quote(string $value): string
    {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    /** @return array<int,int> */
    private function extractSearchUids(string $response): array
    {
        if (preg_match('/\* SEARCH([^\r\n]*)/i', $response, $matches) !== 1) {
            return [];
        }

        $uids = preg_split('/\s+/', trim($matches[1])) ?: [];

        return array_values(array_filter(array_map('intval', $uids), fn (int $uid): bool => $uid > 0));
    }

    /** @return array<string,string> */
    private function parseHeaders(string $raw): array
    {
        $raw = preg_replace("/\r?\n[\t ]+/", ' ', $raw) ?: $raw;
        $headers = [];

        foreach (preg_split('/\r?\n/', trim($raw)) ?: [] as $line) {
            $position = strpos($line, ':');
            if ($position === false) {
                continue;
            }

            $name = strtolower(trim(substr($line, 0, $position)));
            $value = trim(substr($line, $position + 1));

            if (isset($headers[$name])) {
                $headers[$name] .= ', '.$value;
            } else {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    private function firstRecipientHeader(array $headers): string
    {
        foreach (['to', 'delivered-to', 'x-original-to'] as $name) {
            $value = (string) ($headers[$name] ?? '');
            $address = $this->extractEmailAddress($value);
            if ($address !== '') {
                return $address;
            }
        }

        return '';
    }

    private function looksLikeLiveChatAlias(string $recipient, string $username): bool
    {
        $recipient = strtolower($this->extractEmailAddress($recipient));
        $parts = explode('@', strtolower($username), 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$local, $domain] = $parts;

        return preg_match(
            '/^'.preg_quote($local, '/').'\\+lc-[a-z0-9]{32,80}@'.preg_quote($domain, '/').'$/i',
            $recipient
        ) === 1;
    }

    private function extractEmailAddress(string $value): string
    {
        if (preg_match('/<([^>]+)>/', $value, $matches) === 1) {
            return strtolower(trim($matches[1]));
        }

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches) === 1) {
            return strtolower(trim($matches[0]));
        }

        return strtolower(trim($value));
    }

    /**
     * @return array{text:string,attachments:array<int,array{name:string,mime:?string,content:string}>}
     */
    private function parseMimeMessage(string $raw): array
    {
        [$headerRaw, $body] = $this->splitEntity($raw);
        $result = [
            'plain' => [],
            'html' => [],
            'attachments' => [],
        ];

        $this->walkMimePart($this->parseHeaders($headerRaw), $body, $result);

        $text = trim(implode("\n\n", array_filter($result['plain'])));

        if ($text === '' && $result['html'] !== []) {
            $html = implode("\n", $result['html']);
            $html = preg_replace('/<br\s*\/?\s*>/i', "\n", $html) ?: $html;
            $html = preg_replace('/<\/p\s*>/i', "\n\n", $html) ?: $html;
            $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return [
            'text' => $text,
            'attachments' => $result['attachments'],
        ];
    }

    /** @return array{0:string,1:string} */
    private function splitEntity(string $raw): array
    {
        $parts = preg_split('/\r?\n\r?\n/', $raw, 2);

        return [
            (string) ($parts[0] ?? ''),
            (string) ($parts[1] ?? ''),
        ];
    }

    /** @param array{plain:array<int,string>,html:array<int,string>,attachments:array<int,array{name:string,mime:?string,content:string}>} $result */
    private function walkMimePart(array $headers, string $body, array &$result): void
    {
        $contentTypeHeader = (string) ($headers['content-type'] ?? 'text/plain; charset=UTF-8');
        $contentType = strtolower(trim(strtok($contentTypeHeader, ';') ?: 'text/plain'));

        if (str_starts_with($contentType, 'multipart/')) {
            $boundary = $this->headerParameter($contentTypeHeader, 'boundary');
            if ($boundary === null || $boundary === '') {
                return;
            }

            foreach ($this->splitMultipart($body, $boundary) as $part) {
                [$childHeaders, $childBody] = $this->splitEntity($part);
                $this->walkMimePart($this->parseHeaders($childHeaders), $childBody, $result);
            }

            return;
        }

        $encoding = strtolower(trim((string) ($headers['content-transfer-encoding'] ?? '')));
        $decoded = $this->decodeTransfer($body, $encoding);
        $disposition = (string) ($headers['content-disposition'] ?? '');
        $filename = $this->headerParameter($disposition, 'filename')
            ?: $this->headerParameter($contentTypeHeader, 'name');

        if ($filename !== null || str_starts_with(strtolower($disposition), 'attachment')) {
            $result['attachments'][] = [
                'name' => $this->decodeHeader($filename ?: 'bijlage'),
                'mime' => $contentType !== '' ? $contentType : null,
                'content' => $decoded,
            ];
            return;
        }

        if ($contentType === 'text/plain') {
            $result['plain'][] = $this->convertCharset(
                $decoded,
                $this->headerParameter($contentTypeHeader, 'charset') ?: 'UTF-8'
            );
            return;
        }

        if ($contentType === 'text/html') {
            $result['html'][] = $this->convertCharset(
                $decoded,
                $this->headerParameter($contentTypeHeader, 'charset') ?: 'UTF-8'
            );
        }
    }

    /** @return array<int,string> */
    private function splitMultipart(string $body, string $boundary): array
    {
        $delimiter = '--'.$boundary;
        $segments = explode($delimiter, $body);
        $parts = [];

        foreach ($segments as $segment) {
            $segment = ltrim($segment, "\r\n");
            $segment = rtrim($segment, "\r\n");

            if ($segment === '' || $segment === '--') {
                continue;
            }

            if (str_ends_with($segment, '--')) {
                $segment = substr($segment, 0, -2);
            }

            $segment = trim($segment, "\r\n");
            if ($segment !== '' && str_contains($segment, "\n")) {
                $parts[] = $segment;
            }
        }

        return $parts;
    }

    private function decodeTransfer(string $body, string $encoding): string
    {
        return match ($encoding) {
            'base64' => base64_decode(preg_replace('/\s+/', '', $body) ?: '', true) ?: '',
            'quoted-printable' => quoted_printable_decode($body),
            default => $body,
        };
    }

    private function headerParameter(string $header, string $name): ?string
    {
        if (preg_match('/(?:^|;)\s*'.preg_quote($name, '/').'\s*=\s*(?:"([^"]*)"|([^;\s]*))/i', $header, $matches) !== 1) {
            return null;
        }

        return trim((string) ($matches[1] !== '' ? $matches[1] : ($matches[2] ?? '')));
    }

    private function decodeHeader(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (function_exists('iconv_mime_decode')) {
            $decoded = @iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            if (is_string($decoded) && $decoded !== '') {
                return $decoded;
            }
        }

        if (function_exists('mb_decode_mimeheader')) {
            return mb_decode_mimeheader($value);
        }

        return $value;
    }

    private function convertCharset(string $value, string $charset): string
    {
        $charset = trim($charset, " \t\n\r\0\x0B\"'");

        if ($charset === '' || strcasecmp($charset, 'UTF-8') === 0) {
            return $value;
        }

        if (function_exists('mb_convert_encoding')) {
            try {
                return mb_convert_encoding($value, 'UTF-8', $charset);
            } catch (Throwable) {
                return $value;
            }
        }

        return $value;
    }

    private function cleanReplyText(string $text): string
    {
        $text = preg_replace("/\r\n?/", "\n", trim($text)) ?: '';

        $patterns = [
            '/\nOn .{0,300}wrote:\s*\n/is',
            '/\nOp .{0,300}schreef .{0,300}:\s*\n/is',
            '/\nVan:\s.+\nVerzonden:\s.+/is',
            '/\nFrom:\s.+\nSent:\s.+/is',
        ];

        foreach ($patterns as $pattern) {
            $parts = preg_split($pattern, $text, 2);
            if (is_array($parts) && count($parts) > 1) {
                $text = trim($parts[0]);
                break;
            }
        }

        $lines = preg_split('/\n/', $text) ?: [];
        $kept = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*>/', $line) === 1) {
                break;
            }
            $kept[] = rtrim($line);
        }

        return trim(implode("\n", $kept));
    }
}
