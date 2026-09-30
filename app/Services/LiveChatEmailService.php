<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;
use Throwable;

class LiveChatEmailService
{
    public function __construct(
        private readonly LiveChatService $chat
    ) {
    }

    /**
     * Zet een bestaand gesprek over naar e-mail.
     * De admin blijft in hetzelfde live-chatpaneel.
     *
     * @return array{conversation:array<string,mixed>,email_sent:bool,email_error:?string,inbound_ready:bool}
     */
    public function enable(
        int $conversationId,
        string $email,
        int $adminId
    ): array {
        $this->assertEnabled();
        $this->assertBrevoConfigured();
        $this->assertGmailReplyAddressConfigured();

        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(422, 'Het e-mailadres van de klant is ongeldig.');
        }

        $result = DB::transaction(function () use ($conversationId, $email): array {
            $conversation = DB::table('live_chat_conversations')
                ->where('id', $conversationId)
                ->lockForUpdate()
                ->first();

            abort_unless($conversation, 404);

            $wasEmail = (($conversation->delivery_channel ?? 'live') === 'email');
            $token = $conversation->email_thread_token ?: $this->newThreadToken();

            DB::table('live_chat_conversations')
                ->where('id', $conversationId)
                ->update([
                    'delivery_channel' => 'email',
                    'contact_email' => $email,
                    'email_thread_token' => $token,
                    'email_handoff_at' => $conversation->email_handoff_at ?: now(),
                    'status' => $conversation->status === 'closed' ? 'open' : $conversation->status,
                    'updated_at' => now(),
                ]);

            return [
                'was_email' => $wasEmail,
                'conversation' => DB::table('live_chat_conversations')
                    ->where('id', $conversationId)
                    ->first(),
            ];
        });

        $delivery = [
            'sent' => true,
            'error' => null,
        ];

        if (! $result['was_email']) {
            $clientId = (string) Str::uuid();

            $this->chat->sendAdmin(
                $conversationId,
                $adminId,
                [
                    'type' => 'text',
                    'client_id' => $clientId,
                    'body' => (string) config(
                        'live-chat-email.handoff_message',
                        'Uw gesprek gaat vanaf nu verder via e-mail.'
                    ),
                ],
                null
            );

            $delivery = $this->deliverAdminMessage($conversationId, $clientId);
        }

        return [
            'conversation' => $this->conversationState($conversationId),
            'email_sent' => (bool) $delivery['sent'],
            'email_error' => $delivery['error'],
            'inbound_ready' => $this->gmailInboundReady(),
        ];
    }

    /** @return array{conversation:array<string,mixed>} */
    public function disable(int $conversationId): array
    {
        abort_unless(
            DB::table('live_chat_conversations')->where('id', $conversationId)->exists(),
            404
        );

        DB::table('live_chat_conversations')
            ->where('id', $conversationId)
            ->update([
                'delivery_channel' => 'live',
                'email_handoff_at' => null,
                'updated_at' => now(),
            ]);

        return [
            'conversation' => $this->conversationState($conversationId),
        ];
    }

    /**
     * Stuurt een reeds opgeslagen adminbericht via Brevo naar de klant.
     *
     * @return array{sent:bool,skipped:bool,error:?string}
     */
    public function deliverAdminMessage(
        int $conversationId,
        string $clientId
    ): array {
        $conversation = $this->conversationWithUser($conversationId);
        abort_unless($conversation, 404);

        if (($conversation->delivery_channel ?? 'live') !== 'email') {
            return ['sent' => false, 'skipped' => true, 'error' => null];
        }

        $message = DB::table('live_chat_messages')
            ->where('conversation_id', $conversationId)
            ->where('client_id', $clientId)
            ->where('sender', 'admin')
            ->first();

        if (! $message) {
            return [
                'sent' => false,
                'skipped' => false,
                'error' => 'Het opgeslagen adminbericht kon niet worden gevonden.',
            ];
        }

        $email = $this->conversationEmail($conversation);

        if (! $email) {
            $error = 'Dit gesprek heeft geen geldig e-mailadres.';
            $this->markDeliveryFailure((int) $message->id, $error);

            return ['sent' => false, 'skipped' => false, 'error' => $error];
        }

        try {
            $this->assertEnabled();
            $this->assertBrevoConfigured();
            $replyAddress = $this->replyAddress($conversation);

            $bodyText = $this->mailBodyForMessage($message);
            $prefix = trim((string) config('live-chat-email.subject_prefix', 'Mashal Support'));
            $threadToken = trim((string) ($conversation->email_thread_token ?? ''));

            if ($threadToken === '') {
                throw new RuntimeException('Dit gesprek heeft geen e-mailthread-token.');
            }

            $subject = ($prefix ?: 'Mashal Support')
                .' · gesprek #'.$conversationId
                .' [LC:'.$threadToken.']';

            $payload = [
                'sender' => [
                    'email' => $this->fromEmail(),
                    'name' => $this->fromName(),
                ],
                'to' => [[
                    'email' => $email,
                    'name' => $conversation->user_name ?: null,
                ]],
                'replyTo' => [
                    'email' => $replyAddress,
                    'name' => $this->fromName(),
                ],
                'subject' => $subject,
                'textContent' => $bodyText
                    ."\n\nAntwoord gewoon op deze e-mail. Uw antwoord verschijnt in hetzelfde supportgesprek.",
                'htmlContent' => $this->mailHtml(
                    $conversationId,
                    $bodyText,
                    $conversation->user_name ?: null
                ),
                'headers' => [
                    'X-Live-Chat-Conversation' => (string) $conversationId,
                ],
            ];

            $attachments = $this->brevoAttachmentsForMessage($message);
            if ($attachments !== []) {
                $payload['attachment'] = $attachments;
            }

            $response = Http::acceptJson()
                ->asJson()
                ->timeout(max(5, (int) config('live-chat-email.brevo_timeout_seconds', 20)))
                ->withHeaders([
                    'api-key' => $this->brevoApiKey(),
                ])
                ->post((string) config('live-chat-email.brevo_endpoint'), $payload);

            if (! $response->successful()) {
                $remoteMessage = trim((string) ($response->json('message') ?: $response->body()));
                throw new RuntimeException(
                    'Brevo gaf HTTP '.$response->status().($remoteMessage !== '' ? ': '.Str::limit($remoteMessage, 500, '') : '')
                );
            }

            $brevoMessageId = trim((string) $response->json('messageId'));

            DB::table('live_chat_messages')
                ->where('id', $message->id)
                ->update([
                    'email_sent_at' => now(),
                    'email_delivery_error' => null,
                    'email_message_id' => $brevoMessageId !== ''
                        ? Str::limit($brevoMessageId, 190, '')
                        : ($message->email_message_id ?? null),
                    'source' => $message->source ?? 'live',
                ]);

            return ['sent' => true, 'skipped' => false, 'error' => null];
        } catch (Throwable $exception) {
            $error = Str::limit(
                $exception->getMessage() ?: 'De e-mail kon niet worden verstuurd.',
                1800,
                ''
            );

            $this->markDeliveryFailure((int) $message->id, $error);

            Log::error('Live-chat Brevo e-mail kon niet worden verstuurd.', [
                'conversation_id' => $conversationId,
                'message_id' => $message->id,
                'exception' => $exception,
            ]);

            return ['sent' => false, 'skipped' => false, 'error' => $error];
        }
    }

    /**
     * Verwerkt één door Gmail/IMAP opgehaald bericht.
     *
     * @param array{
     *   recipient:string,
     *   from:string,
     *   message_id:string,
     *   in_reply_to:?string,
     *   subject?:string,
     *   body:string,
     *   attachments?:array<int,array{name:string,mime:?string,content:string}>
     * } $mail
     * @return array{ok:bool,duplicate?:bool,inserted?:int,conversation_id?:int}
     */
    public function receiveGmailMessage(array $mail): array
    {
        $this->assertEnabled();

        $messageId = Str::limit(trim((string) ($mail['message_id'] ?? '')), 190, '');
        if ($messageId === '') {
            $messageId = 'gmail:'.hash('sha256', json_encode($mail));
        }

        if (DB::table('live_chat_messages')->where('email_message_id', $messageId)->exists()) {
            return ['ok' => true, 'duplicate' => true];
        }

        $conversation = $this->conversationFromInboundMail(
            (string) ($mail['recipient'] ?? ''),
            (string) ($mail['in_reply_to'] ?? ''),
            (string) ($mail['subject'] ?? '')
        );

        abort_unless($conversation, 404, 'Geen live-chatgesprek gevonden voor deze e-mail.');

        if (($conversation->delivery_channel ?? 'live') !== 'email') {
            abort(409, 'Dit gesprek staat niet meer in e-mailmodus.');
        }

        $sender = strtolower(trim((string) ($mail['from'] ?? '')));
        $this->assertAllowedSender($conversation, $sender);

        $body = $this->limitIncomingBody((string) ($mail['body'] ?? ''));
        $attachments = is_array($mail['attachments'] ?? null) ? $mail['attachments'] : [];
        $inReplyTo = Str::limit(trim((string) ($mail['in_reply_to'] ?? '')), 190, '');

        if ($body === '' && $attachments === []) {
            return [
                'ok' => true,
                'inserted' => 0,
                'conversation_id' => (int) $conversation->id,
            ];
        }

        $inserted = DB::transaction(function () use (
            $conversation,
            $body,
            $attachments,
            $messageId,
            $inReplyTo
        ): int {
            $count = 0;
            $firstIdUsed = false;

            if ($body !== '') {
                $this->insertInboundText(
                    $conversation,
                    $body,
                    $messageId,
                    $inReplyTo !== '' ? $inReplyTo : null
                );
                $count++;
                $firstIdUsed = true;
            }

            foreach ($attachments as $index => $attachment) {
                if (! is_array($attachment)) {
                    continue;
                }

                $attachmentMessageId = $firstIdUsed
                    ? $messageId.':attachment:'.($index + 1)
                    : ($index === 0 ? $messageId : $messageId.':attachment:'.($index + 1));

                if ($this->insertInboundAttachment(
                    $conversation,
                    $attachment,
                    Str::limit($attachmentMessageId, 190, ''),
                    $inReplyTo !== '' ? $inReplyTo : null
                )) {
                    $count++;
                    $firstIdUsed = true;
                }
            }

            DB::table('live_chat_conversations')
                ->where('id', $conversation->id)
                ->update([
                    'delivery_channel' => 'email',
                    'status' => 'open',
                    'last_message_at' => now(),
                    'updated_at' => now(),
                ]);

            return $count;
        });

        return [
            'ok' => true,
            'inserted' => $inserted,
            'conversation_id' => (int) $conversation->id,
        ];
    }

    /** @return array<string,mixed> */
    public function conversationState(int $conversationId): array
    {
        $conversation = $this->conversationWithUser($conversationId);
        abort_unless($conversation, 404);

        return [
            'id' => (int) $conversation->id,
            'status' => $conversation->status,
            'delivery_channel' => $conversation->delivery_channel ?? 'live',
            'email' => $this->conversationEmail($conversation),
            'email_handoff_at' => $conversation->email_handoff_at ?? null,
            'email_inbound_ready' => $this->gmailInboundReady(),
        ];
    }

    public function gmailInboundReady(): bool
    {
        return $this->gmailUsername() !== ''
            && trim((string) config('live-chat-email.gmail_app_password')) !== '';
    }

    private function conversationFromInboundMail(
        string $recipient,
        string $inReplyTo,
        string $subject = ''
    ): ?stdClass {
        // Backwards compatibel met eerder gebruikte Gmail +lc- aliassen.
        $token = $this->threadTokenFromGmailAlias($recipient);

        if ($token !== null) {
            $conversation = $this->conversationWithUserByToken($token);
            if ($conversation) {
                return $conversation;
            }
        }

        // Nieuwe methode: thread-token staat in het onderwerp.
        $token = $this->threadTokenFromSubject($subject);

        if ($token !== null) {
            $conversation = $this->conversationWithUserByToken($token);
            if ($conversation) {
                return $conversation;
            }
        }

        // Extra fallback: standaard e-mailthread via Message-ID / In-Reply-To.
        $inReplyTo = trim($inReplyTo);
        if ($inReplyTo !== '') {
            $message = DB::table('live_chat_messages')
                ->where('email_message_id', Str::limit($inReplyTo, 190, ''))
                ->first(['conversation_id']);

            if ($message) {
                return $this->conversationWithUser((int) $message->conversation_id);
            }
        }

        return null;
    }

    private function conversationWithUser(int $conversationId): ?stdClass
    {
        return DB::table('live_chat_conversations as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.id', $conversationId)
            ->select('c.*', 'u.name as user_name', 'u.email as user_email')
            ->first();
    }

    private function conversationWithUserByToken(string $threadToken): ?stdClass
    {
        return DB::table('live_chat_conversations as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.email_thread_token', $threadToken)
            ->select('c.*', 'u.name as user_name', 'u.email as user_email')
            ->first();
    }

    private function conversationEmail(stdClass $conversation): ?string
    {
        $email = strtolower(trim((string) (
            $conversation->contact_email
            ?: $conversation->user_email
            ?: ''
        )));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function replyAddress(stdClass $conversation): string
    {
        // Brevo weigert in sommige accounts dynamische Gmail +tag adressen
        // als replyTo. Gebruik daarom het echte Gmail-adres.
        $gmail = $this->gmailUsername();

        if (! filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
            abort(503, 'LIVE_CHAT_GMAIL_USERNAME is ongeldig.');
        }

        return $gmail;
    }

    private function threadTokenFromGmailAlias(string $recipient): ?string
    {
        $recipient = strtolower(trim($this->extractEmailAddress($recipient)));
        $username = strtolower($this->gmailUsername());

        if ($recipient === '' || $username === '') {
            return null;
        }

        $parts = explode('@', $username, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$local, $domain] = $parts;

        $matched = preg_match(
            '/^'.preg_quote($local, '/').'\\+lc-([a-z0-9]{32,80})@'.preg_quote($domain, '/').'$/i',
            $recipient,
            $matches
        );

        return $matched === 1 ? $matches[1] : null;
    }

    private function threadTokenFromSubject(string $subject): ?string
    {
        $subject = trim($subject);

        if ($subject === '') {
            return null;
        }

        $matched = preg_match(
            '/\[LC:([A-Za-z0-9]{32,80})\]/',
            $subject,
            $matches
        );

        return $matched === 1 ? $matches[1] : null;
    }

    private function newThreadToken(): string
    {
        do {
            $token = Str::random(48);
        } while (DB::table('live_chat_conversations')->where('email_thread_token', $token)->exists());

        return $token;
    }

    private function mailHtml(
        int $conversationId,
        string $bodyText,
        ?string $customerName
    ): string {
        $safeBody = nl2br(
            htmlspecialchars($bodyText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
        $safeName = $customerName
            ? htmlspecialchars($customerName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            : '';
        $greeting = $safeName !== ''
            ? '<p style="margin:0 0 18px">Hallo '.$safeName.',</p>'
            : '';

        return '<!doctype html><html lang="nl"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>Mashal Support</title></head>'
            .'<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 12px"><tr><td align="center">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb">'
            .'<tr><td style="padding:22px 24px;background:#111827;color:#fff"><strong style="font-size:18px">Mashal Support</strong>'
            .'<div style="margin-top:4px;font-size:12px;opacity:.75">Gesprek #'.$conversationId.'</div></td></tr>'
            .'<tr><td style="padding:26px 24px">'.$greeting
            .'<div style="font-size:15px;line-height:1.6">'.$safeBody.'</div>'
            .'<div style="margin-top:28px;padding:14px 16px;border-radius:10px;background:#f3f4f6;font-size:13px;line-height:1.5;color:#4b5563">'
            .'Antwoord gewoon op deze e-mail. Uw antwoord verschijnt automatisch in hetzelfde supportgesprek bij de medewerker.'
            .'</div></td></tr></table></td></tr></table></body></html>';
    }

    private function mailBodyForMessage(stdClass $message): string
    {
        $body = trim((string) ($message->body ?? ''));

        if ($body !== '') {
            return $body;
        }

        return match ($message->type ?? 'text') {
            'voice' => 'Mashal Support heeft een spraakbericht gestuurd.',
            'file' => 'Mashal Support heeft een bestand gestuurd.',
            default => 'Mashal Support heeft een nieuw bericht gestuurd.',
        };
    }

    /** @return array<int,array{name:string,content:string}> */
    private function brevoAttachmentsForMessage(stdClass $message): array
    {
        if (empty($message->attachment_path)) {
            return [];
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($message->attachment_path)) {
            return [];
        }

        $contents = $disk->get($message->attachment_path);

        return [[
            'name' => $message->attachment_name ?: basename($message->attachment_path),
            'content' => base64_encode($contents),
        ]];
    }

    private function insertInboundText(
        stdClass $conversation,
        string $body,
        string $emailMessageId,
        ?string $inReplyTo
    ): void {
        DB::table('live_chat_messages')->insert([
            'conversation_id' => $conversation->id,
            'client_id' => (string) Str::uuid(),
            'sender' => 'visitor',
            'sender_user_id' => $conversation->user_id ? (int) $conversation->user_id : null,
            'type' => 'text',
            'body' => $body,
            'attachment_path' => null,
            'attachment_name' => null,
            'attachment_mime' => null,
            'attachment_size' => null,
            'source' => 'email',
            'email_message_id' => $emailMessageId,
            'email_in_reply_to' => $inReplyTo,
            'created_at' => now(),
        ]);
    }

    /** @param array{name?:string,mime?:?string,content?:string} $attachment */
    private function insertInboundAttachment(
        stdClass $conversation,
        array $attachment,
        string $emailMessageId,
        ?string $inReplyTo
    ): bool {
        $content = (string) ($attachment['content'] ?? '');
        $size = strlen($content);
        $maxBytes = max(1024, (int) config('live-chat-email.max_attachment_bytes', 20 * 1024 * 1024));

        if ($content === '' || $size > $maxBytes) {
            return false;
        }

        $name = $this->safeAttachmentName((string) ($attachment['name'] ?? 'bijlage'));
        $path = 'live-chat/email-attachments/'.Str::uuid().'-'.$name;
        Storage::disk('public')->put($path, $content);

        DB::table('live_chat_messages')->insert([
            'conversation_id' => $conversation->id,
            'client_id' => (string) Str::uuid(),
            'sender' => 'visitor',
            'sender_user_id' => $conversation->user_id ? (int) $conversation->user_id : null,
            'type' => 'file',
            'body' => '',
            'attachment_path' => $path,
            'attachment_name' => $name,
            'attachment_mime' => $attachment['mime'] ?: null,
            'attachment_size' => $size,
            'source' => 'email',
            'email_message_id' => $emailMessageId,
            'email_in_reply_to' => $inReplyTo,
            'created_at' => now(),
        ]);

        return true;
    }

    private function safeAttachmentName(string $name): string
    {
        $name = basename(trim($name));
        $name = preg_replace('/[^A-Za-z0-9._ -]+/u', '_', $name) ?: 'bijlage';

        return Str::limit($name, 180, '');
    }

    private function limitIncomingBody(string $body): string
    {
        $body = preg_replace("/\r\n?/", "\n", trim($body)) ?: '';
        $max = max(500, (int) config('live-chat-email.max_inbound_body_length', 12000));

        return Str::limit($body, $max, '');
    }

    private function assertAllowedSender(stdClass $conversation, string $sender): void
    {
        if (! config('live-chat-email.strict_sender_match', true)) {
            return;
        }

        $sender = strtolower(trim($this->extractEmailAddress($sender)));
        $expected = $this->conversationEmail($conversation);

        abort_unless(
            $expected
            && filter_var($sender, FILTER_VALIDATE_EMAIL)
            && hash_equals(strtolower($expected), $sender),
            403,
            'Dit afzenderadres hoort niet bij dit gesprek.'
        );
    }

    private function extractEmailAddress(string $value): string
    {
        if (preg_match('/<([^>]+)>/', $value, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches) === 1) {
            return trim($matches[0]);
        }

        return trim($value);
    }

    private function markDeliveryFailure(int $messageId, string $error): void
    {
        DB::table('live_chat_messages')
            ->where('id', $messageId)
            ->update([
                'email_delivery_error' => Str::limit($error, 1800, ''),
            ]);
    }

    private function assertEnabled(): void
    {
        if (! config('live-chat-email.enabled', true)) {
            abort(503, 'E-mailhandoff is uitgeschakeld.');
        }
    }

    private function assertBrevoConfigured(): void
    {
        abort_if($this->brevoApiKey() === '', 503, 'BREVO_API_KEY ontbreekt.');
        abort_if(! filter_var($this->fromEmail(), FILTER_VALIDATE_EMAIL), 503, 'BREVO_FROM_EMAIL is ongeldig.');
    }

    private function assertGmailReplyAddressConfigured(): void
    {
        $gmail = strtolower($this->gmailUsername());

        abort_unless(
            filter_var($gmail, FILTER_VALIDATE_EMAIL)
            && (str_ends_with($gmail, '@gmail.com') || str_ends_with($gmail, '@googlemail.com')),
            503,
            'Gebruik voor LIVE_CHAT_GMAIL_USERNAME een Gmail-adres.'
        );
    }

    private function brevoApiKey(): string
    {
        return trim((string) config('live-chat-email.brevo_api_key'));
    }

    private function fromEmail(): string
    {
        return strtolower(trim((string) config('live-chat-email.from_email')));
    }

    private function fromName(): string
    {
        $name = trim((string) config('live-chat-email.from_name', 'Mashal Support'));
        return $name !== '' ? $name : 'Mashal Support';
    }

    private function gmailUsername(): string
    {
        return strtolower(trim((string) config('live-chat-email.gmail_username')));
    }
}
