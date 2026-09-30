<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GmailLiveChatInboxService
{
    public function __construct(
        private readonly GmailApiClient $gmail,
        private readonly LiveChatEmailService $email
    ) {
    }

    /**
     * @return array{
     *   ok:bool,
     *   configured:bool,
     *   checked:int,
     *   inserted:int,
     *   duplicates:int,
     *   errors:int,
     *   message?:string
     * }
     */
    public function sync(
        ?int $requestedLimit = null
    ): array {
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

        if (! $this->gmail->configured()) {
            return [
                'ok' => true,
                'configured' => false,
                'checked' => 0,
                'inserted' => 0,
                'duplicates' => 0,
                'errors' => 0,
                'message' => 'Gmail API is nog niet gekoppeld. Open /admin/live-chat/gmail/connect.',
            ];
        }

        $limit = max(
            1,
            min(
                100,
                $requestedLimit
                    ?: (int) config(
                        'live-chat-email.gmail_sync_limit',
                        25
                    )
            )
        );

        $items = $this->gmail->listUnread(
            $limit
        );

        $checked = 0;
        $inserted = 0;
        $duplicates = 0;
        $errors = 0;

        foreach ($items as $item) {
            $gmailMessageId = trim(
                (string) ($item['id'] ?? '')
            );

            if ($gmailMessageId === '') {
                continue;
            }

            try {
                $message = $this->gmail->getMessage(
                    $gmailMessageId,
                    'full'
                );

                $gmailThreadId = trim(
                    (string) (
                        $message['threadId']
                        ?? $item['threadId']
                        ?? ''
                    )
                );

                $payload = (array) (
                    $message['payload'] ?? []
                );

                $headers = $this->headersFromPayload(
                    $payload
                );

                $subject = trim(
                    (string) (
                        $headers['subject']
                        ?? ''
                    )
                );

                if (
                    ! $this->looksLikeLiveChatMail(
                        $gmailThreadId,
                        $subject
                    )
                ) {
                    continue;
                }

                $checked++;

                $parsed = $this->parsePayload(
                    $gmailMessageId,
                    $payload
                );

                $result = $this->email->receiveGmailMessage([
                    'gmail_message_id' => $gmailMessageId,
                    'gmail_thread_id' => $gmailThreadId,
                    'recipient' => $this->extractEmailAddress(
                        (string) (
                            $headers['to']
                            ?? ''
                        )
                    ),
                    'from' => $this->extractEmailAddress(
                        (string) (
                            $headers['from']
                            ?? ''
                        )
                    ),
                    'message_id' => trim(
                        (string) (
                            $headers['message-id']
                            ?? ''
                        )
                    ),
                    'in_reply_to' => trim(
                        (string) (
                            $headers['in-reply-to']
                            ?? ''
                        )
                    ) ?: null,
                    'subject' => $subject,
                    'body' => $this->cleanReplyText(
                        $parsed['text']
                    ),
                    'attachments' => $parsed[
                        'attachments'
                    ],
                ]);

                $inserted += (int) (
                    $result['inserted']
                    ?? 0
                );

                if (
                    ($result['duplicate'] ?? false)
                    === true
                ) {
                    $duplicates++;
                }

                // Pas NA succesvolle import/duplicate-detectie aanpassen.
                // Zo verdwijnt nooit een klantmail uit Inbox voordat SmartDesk
                // hem veilig heeft verwerkt.
                $this->gmail->markProcessed(
                    $gmailMessageId,
                    (bool) config(
                        'live-chat-email.gmail_archive_imported',
                        true
                    )
                );
            } catch (Throwable $exception) {
                $errors++;

                Log::warning(
                    'Live-chat Gmail API reply kon niet worden verwerkt.',
                    [
                        'gmail_message_id' => $gmailMessageId,
                        'exception' => $exception,
                    ]
                );
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
    }

    private function looksLikeLiveChatMail(
        string $gmailThreadId,
        string $subject
    ): bool {
        if ($gmailThreadId !== '') {
            $exists = DB::table(
                'live_chat_conversations'
            )
                ->where(
                    'delivery_channel',
                    'email'
                )
                ->where(
                    'gmail_thread_id',
                    $gmailThreadId
                )
                ->exists();

            if ($exists) {
                return true;
            }
        }

        return preg_match(
            '/\[LC:[A-Za-z0-9]{32,80}\]|gesprek\s*#\d+/i',
            $subject
        ) === 1;
    }

    /**
     * @return array<string,string>
     */
    private function headersFromPayload(
        array $payload
    ): array {
        $headers = [];

        foreach (
            (array) ($payload['headers'] ?? [])
            as $header
        ) {
            if (! is_array($header)) {
                continue;
            }

            $name = strtolower(
                trim((string) ($header['name'] ?? ''))
            );

            if ($name === '') {
                continue;
            }

            $headers[$name] = trim(
                (string) ($header['value'] ?? '')
            );
        }

        return $headers;
    }

    /**
     * @return array{
     *   text:string,
     *   attachments:array<int,array{
     *     name:string,
     *     mime:?string,
     *     content:string
     *   }>
     * }
     */
    private function parsePayload(
        string $gmailMessageId,
        array $payload
    ): array {
        $plain = [];
        $html = [];
        $attachments = [];

        $this->walkPart(
            $gmailMessageId,
            $payload,
            $plain,
            $html,
            $attachments
        );

        $text = trim(
            implode(
                "\n\n",
                array_filter($plain)
            )
        );

        if ($text === '' && $html !== []) {
            $combined = implode(
                "\n",
                $html
            );

            $combined = preg_replace(
                '/<br\s*\/?\s*>/i',
                "\n",
                $combined
            ) ?: $combined;

            $combined = preg_replace(
                '/<\/p\s*>/i',
                "\n\n",
                $combined
            ) ?: $combined;

            $text = trim(
                html_entity_decode(
                    strip_tags($combined),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                )
            );
        }

        return [
            'text' => $text,
            'attachments' => $attachments,
        ];
    }

    /**
     * @param array<int,string> $plain
     * @param array<int,string> $html
     * @param array<int,array{name:string,mime:?string,content:string}> $attachments
     */
    private function walkPart(
        string $gmailMessageId,
        array $part,
        array &$plain,
        array &$html,
        array &$attachments
    ): void {
        $mime = strtolower(
            trim(
                (string) (
                    $part['mimeType']
                    ?? 'application/octet-stream'
                )
            )
        );

        $filename = trim(
            (string) (
                $part['filename']
                ?? ''
            )
        );

        $body = (array) (
            $part['body']
            ?? []
        );

        $content = '';

        if (
            isset($body['data'])
            && is_string($body['data'])
            && $body['data'] !== ''
        ) {
            $content = $this->gmail->base64UrlDecode(
                $body['data']
            );
        } elseif (
            $filename !== ''
            && ! empty($body['attachmentId'])
        ) {
            $content = $this->gmail->getAttachment(
                $gmailMessageId,
                (string) $body['attachmentId']
            );
        }

        if ($filename !== '') {
            if ($content !== '') {
                $attachments[] = [
                    'name' => $filename,
                    'mime' => $mime !== ''
                        ? $mime
                        : null,
                    'content' => $content,
                ];
            }

            return;
        }

        if ($mime === 'text/plain' && $content !== '') {
            $plain[] = $content;
        } elseif (
            $mime === 'text/html'
            && $content !== ''
        ) {
            $html[] = $content;
        }

        foreach (
            (array) ($part['parts'] ?? [])
            as $child
        ) {
            if (! is_array($child)) {
                continue;
            }

            $this->walkPart(
                $gmailMessageId,
                $child,
                $plain,
                $html,
                $attachments
            );
        }
    }

    private function extractEmailAddress(
        string $value
    ): string {
        if (
            preg_match(
                '/<([^>]+)>/',
                $value,
                $matches
            ) === 1
        ) {
            return strtolower(
                trim($matches[1])
            );
        }

        if (
            preg_match(
                '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',
                $value,
                $matches
            ) === 1
        ) {
            return strtolower(
                trim($matches[0])
            );
        }

        return strtolower(
            trim($value)
        );
    }

    private function cleanReplyText(
        string $text
    ): string {
        $text = preg_replace(
            "/\r\n?/",
            "\n",
            trim($text)
        ) ?: '';

        $patterns = [
            '/\nOn .{0,300}wrote:\s*\n/is',
            '/\nOp .{0,300}schreef .{0,300}:\s*\n/is',
            '/\nVan:\s.+\nVerzonden:\s.+/is',
            '/\nFrom:\s.+\nSent:\s.+/is',
        ];

        foreach ($patterns as $pattern) {
            $parts = preg_split(
                $pattern,
                $text,
                2
            );

            if (
                is_array($parts)
                && count($parts) > 1
            ) {
                $text = trim($parts[0]);
                break;
            }
        }

        $lines = preg_split(
            '/\n/',
            $text
        ) ?: [];

        $kept = [];

        foreach ($lines as $line) {
            if (
                preg_match(
                    '/^\s*>/',
                    $line
                ) === 1
            ) {
                break;
            }

            $kept[] = rtrim($line);
        }

        return trim(
            implode(
                "\n",
                $kept
            )
        );
    }
}
