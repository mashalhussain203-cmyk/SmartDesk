<?php

namespace App\Services;

use App\Mail\LiveChatThreadMail;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use stdClass;
use Throwable;

class LiveChatEmailService
{
    public function __construct(
        private readonly LiveChatService $chat
    ) {
    }

    /**
     * Zet een bestaand live-chatgesprek over naar e-mail.
     *
     * De admin blijft in hetzelfde adminpaneel werken.
     * Alleen de aflevermethode voor de klant wordt e-mail.
     *
     * @return array{
     *     conversation: array<string, mixed>,
     *     email_sent: bool,
     *     email_error: string|null
     * }
     */
    public function enable(
        int $conversationId,
        string $email,
        int $adminId
    ): array {
        $this->assertEnabled();
        $this->assertReplyDomainConfigured();

        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(
                422,
                'Het e-mailadres van de klant is ongeldig.'
            );
        }

        $result = DB::transaction(
            function () use (
                $conversationId,
                $email
            ): array {
                $conversation = DB::table(
                    'live_chat_conversations'
                )
                    ->where('id', $conversationId)
                    ->lockForUpdate()
                    ->first();

                abort_unless($conversation, 404);

                $wasEmail = (
                    ($conversation->delivery_channel ?? 'live')
                    === 'email'
                );

                $token = $conversation->email_thread_token
                    ?: $this->newThreadToken();

                DB::table('live_chat_conversations')
                    ->where('id', $conversationId)
                    ->update([
                        'delivery_channel' => 'email',
                        'contact_email' => $email,
                        'email_thread_token' => $token,
                        'email_handoff_at' => $conversation->email_handoff_at
                            ?: now(),
                        'status' => $conversation->status === 'closed'
                            ? 'open'
                            : $conversation->status,
                        'updated_at' => now(),
                    ]);

                return [
                    'was_email' => $wasEmail,
                    'conversation' => DB::table(
                        'live_chat_conversations'
                    )
                        ->where('id', $conversationId)
                        ->first(),
                ];
            }
        );

        /** @var stdClass $conversation */
        $conversation = $result['conversation'];

        $delivery = [
            'sent' => true,
            'error' => null,
        ];

        /*
         * Alleen bij de overgang live -> e-mail sturen we automatisch
         * het eerste handoffbericht. Als de admin dezelfde e-mailmodus
         * opnieuw opslaat, versturen we geen dubbel welkomstbericht.
         */
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

            $delivery = $this->deliverAdminMessage(
                $conversationId,
                $clientId
            );
        }

        return [
            'conversation' => $this->conversationState(
                $conversationId
            ),
            'email_sent' => (bool) $delivery['sent'],
            'email_error' => $delivery['error'],
        ];
    }

    /**
     * Zet de aflevermethode terug naar normale live-chat.
     *
     * @return array{conversation: array<string, mixed>}
     */
    public function disable(int $conversationId): array
    {
        DB::table('live_chat_conversations')
            ->where('id', $conversationId)
            ->update([
                'delivery_channel' => 'live',
                'email_handoff_at' => null,
                'updated_at' => now(),
            ]);

        abort_unless(
            DB::table('live_chat_conversations')
                ->where('id', $conversationId)
                ->exists(),
            404
        );

        return [
            'conversation' => $this->conversationState(
                $conversationId
            ),
        ];
    }

    /**
     * Stuurt een reeds opgeslagen adminbericht ook per e-mail naar de klant.
     *
     * De chatopslag blijft leidend. Een mailfout verwijdert het chatbericht
     * dus niet; de fout wordt op het bericht opgeslagen en teruggegeven.
     *
     * @return array{sent: bool, skipped: bool, error: string|null}
     */
    public function deliverAdminMessage(
        int $conversationId,
        string $clientId
    ): array {
        $conversation = $this->conversationWithUser(
            $conversationId
        );

        abort_unless($conversation, 404);

        if (
            ($conversation->delivery_channel ?? 'live')
            !== 'email'
        ) {
            return [
                'sent' => false,
                'skipped' => true,
                'error' => null,
            ];
        }

        if (! config('live-chat-email.enabled', true)) {
            return [
                'sent' => false,
                'skipped' => false,
                'error' => 'E-mailhandoff is uitgeschakeld in de configuratie.',
            ];
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

        $email = $this->conversationEmail(
            $conversation
        );

        if (! $email) {
            $error = 'Dit gesprek heeft geen geldig e-mailadres.';

            $this->markDeliveryFailure(
                (int) $message->id,
                $error
            );

            return [
                'sent' => false,
                'skipped' => false,
                'error' => $error,
            ];
        }

        try {
            $replyAddress = $this->replyAddress(
                $conversation
            );

            Mail::to($email)->send(
                new LiveChatThreadMail(
                    conversationId: $conversationId,
                    bodyText: $this->mailBodyForMessage(
                        $message
                    ),
                    replyAddress: $replyAddress,
                    customerName: $conversation->user_name
                        ?: null,
                    files: $this->mailAttachmentsForMessage(
                        $message
                    ),
                )
            );

            DB::table('live_chat_messages')
                ->where('id', $message->id)
                ->update([
                    'email_sent_at' => now(),
                    'email_delivery_error' => null,
                    'source' => $message->source ?? 'live',
                ]);

            return [
                'sent' => true,
                'skipped' => false,
                'error' => null,
            ];
        } catch (Throwable $exception) {
            $error = Str::limit(
                $exception->getMessage()
                    ?: 'De e-mail kon niet worden verstuurd.',
                1800,
                ''
            );

            $this->markDeliveryFailure(
                (int) $message->id,
                $error
            );

            Log::error(
                'Live-chat e-mail kon niet worden verstuurd.',
                [
                    'conversation_id' => $conversationId,
                    'message_id' => $message->id,
                    'exception' => $exception,
                ]
            );

            return [
                'sent' => false,
                'skipped' => false,
                'error' => $error,
            ];
        }
    }

    /**
     * Verwerkt een door Mailgun doorgestuurde inkomende e-mail.
     *
     * @return array{ok: bool, duplicate?: bool, inserted?: int}
     */
    public function receiveMailgun(
        Request $request,
        string $routeSecret
    ): array {
        $this->assertEnabled();
        $this->assertInboundSecret($routeSecret);

        $webhookToken = $this->verifyMailgunSignature(
            $request
        );

        $recipient = strtolower(
            trim(
                (string) $request->input(
                    'recipient',
                    $request->input('To', '')
                )
            )
        );

        $threadToken = $this->threadTokenFromRecipient(
            $recipient
        );

        $conversation = $this->conversationWithUserByToken(
            $threadToken
        );

        abort_unless($conversation, 404);

        if (
            ($conversation->delivery_channel ?? 'live')
            !== 'email'
        ) {
            abort(409, 'Dit gesprek staat niet meer in e-mailmodus.');
        }

        $sender = strtolower(
            trim(
                (string) $request->input(
                    'sender',
                    $request->input('from', '')
                )
            )
        );

        $this->assertAllowedSender(
            $conversation,
            $sender
        );

        $messageId = $this->mailgunMessageId(
            $request
        ) ?: 'mailgun:'.$webhookToken;

        if (
            DB::table('live_chat_messages')
                ->where('email_message_id', $messageId)
                ->exists()
        ) {
            return [
                'ok' => true,
                'duplicate' => true,
            ];
        }

        $body = $this->incomingBody(
            $request
        );

        $files = $this->incomingFiles(
            $request
        );

        if ($body === '' && $files === []) {
            return [
                'ok' => true,
                'inserted' => 0,
            ];
        }

        $inserted = DB::transaction(
            function () use (
                $conversation,
                $body,
                $files,
                $messageId
            ): int {
                $count = 0;
                $firstMessageIdUsed = false;

                if ($body !== '') {
                    $this->insertInboundMessage(
                        conversation: $conversation,
                        type: 'text',
                        body: $body,
                        emailMessageId: $messageId,
                        file: null
                    );

                    $count++;
                    $firstMessageIdUsed = true;
                }

                foreach ($files as $index => $file) {
                    $attachmentMessageId = $firstMessageIdUsed
                        ? $messageId.':attachment:'.($index + 1)
                        : (
                            $index === 0
                                ? $messageId
                                : $messageId.':attachment:'.($index + 1)
                        );

                    if (
                        $this->insertInboundMessage(
                            conversation: $conversation,
                            type: 'file',
                            body: '',
                            emailMessageId: $attachmentMessageId,
                            file: $file
                        )
                    ) {
                        $count++;
                        $firstMessageIdUsed = true;
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
            }
        );

        return [
            'ok' => true,
            'inserted' => $inserted,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function conversationState(
        int $conversationId
    ): array {
        $conversation = $this->conversationWithUser(
            $conversationId
        );

        abort_unless($conversation, 404);

        return [
            'id' => (int) $conversation->id,
            'status' => $conversation->status,
            'delivery_channel' => $conversation->delivery_channel
                ?? 'live',
            'email' => $this->conversationEmail(
                $conversation
            ),
            'email_handoff_at' => $conversation->email_handoff_at
                ?? null,
        ];
    }

    private function conversationWithUser(
        int $conversationId
    ): ?stdClass {
        return DB::table('live_chat_conversations as c')
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'c.user_id'
            )
            ->where('c.id', $conversationId)
            ->select(
                'c.*',
                'u.name as user_name',
                'u.email as user_email'
            )
            ->first();
    }

    private function conversationWithUserByToken(
        string $threadToken
    ): ?stdClass {
        return DB::table('live_chat_conversations as c')
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'c.user_id'
            )
            ->where(
                'c.email_thread_token',
                $threadToken
            )
            ->select(
                'c.*',
                'u.name as user_name',
                'u.email as user_email'
            )
            ->first();
    }

    private function conversationEmail(
        stdClass $conversation
    ): ?string {
        $email = strtolower(
            trim(
                (string) (
                    $conversation->contact_email
                    ?: $conversation->user_email
                    ?: ''
                )
            )
        );

        return filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
            ? $email
            : null;
    }

    private function replyAddress(
        stdClass $conversation
    ): string {
        $this->assertReplyDomainConfigured();

        $token = trim(
            (string) (
                $conversation->email_thread_token
                ?? ''
            )
        );

        if ($token === '') {
            abort(
                503,
                'Dit gesprek heeft geen e-mailthread-token.'
            );
        }

        $domain = strtolower(
            trim(
                (string) config(
                    'live-chat-email.reply_domain'
                )
            )
        );

        return 'reply+'.$token.'@'.$domain;
    }

    private function newThreadToken(): string
    {
        do {
            $token = Str::random(48);
        } while (
            DB::table('live_chat_conversations')
                ->where(
                    'email_thread_token',
                    $token
                )
                ->exists()
        );

        return $token;
    }

    private function mailBodyForMessage(
        stdClass $message
    ): string {
        $body = trim(
            (string) ($message->body ?? '')
        );

        if ($body !== '') {
            return $body;
        }

        return match (
            $message->type ?? 'text'
        ) {
            'voice' => 'Mashal Support heeft een spraakbericht gestuurd.',
            'file' => 'Mashal Support heeft een bestand gestuurd.',
            default => 'Mashal Support heeft een nieuw bericht gestuurd.',
        };
    }

    /**
     * @return array<int, array{disk:string,path:string,name:string,mime:string|null}>
     */
    private function mailAttachmentsForMessage(
        stdClass $message
    ): array {
        if (empty($message->attachment_path)) {
            return [];
        }

        if (
            ! Storage::disk('public')->exists(
                $message->attachment_path
            )
        ) {
            return [];
        }

        return [[
            'disk' => 'public',
            'path' => $message->attachment_path,
            'name' => $message->attachment_name
                ?: basename($message->attachment_path),
            'mime' => $message->attachment_mime
                ?: null,
        ]];
    }

    private function markDeliveryFailure(
        int $messageId,
        string $error
    ): void {
        DB::table('live_chat_messages')
            ->where('id', $messageId)
            ->update([
                'email_delivery_error' => Str::limit(
                    $error,
                    1800,
                    ''
                ),
            ]);
    }

    private function assertEnabled(): void
    {
        if (! config('live-chat-email.enabled', true)) {
            abort(
                503,
                'E-mailhandoff is uitgeschakeld.'
            );
        }
    }

    private function assertReplyDomainConfigured(): void
    {
        $domain = trim(
            (string) config(
                'live-chat-email.reply_domain'
            )
        );

        if (
            $domain === ''
            || str_contains($domain, '@')
        ) {
            abort(
                503,
                'LIVE_CHAT_REPLY_DOMAIN is niet correct ingesteld.'
            );
        }
    }

    private function assertInboundSecret(
        string $routeSecret
    ): void {
        $expected = (string) config(
            'live-chat-email.inbound_secret'
        );

        abort_if(
            $expected === '',
            503,
            'LIVE_CHAT_INBOUND_SECRET ontbreekt.'
        );

        abort_unless(
            hash_equals(
                $expected,
                $routeSecret
            ),
            403
        );
    }

    /**
     * Geeft de unieke Mailgun webhook-token terug na verificatie.
     */
    private function verifyMailgunSignature(
        Request $request
    ): string {
        $key = (string) config(
            'live-chat-email.mailgun_signing_key'
        );

        abort_if(
            $key === '',
            503,
            'MAILGUN_WEBHOOK_SIGNING_KEY ontbreekt.'
        );

        $timestamp = (string) $request->input(
            'timestamp',
            ''
        );

        $token = (string) $request->input(
            'token',
            ''
        );

        $signature = (string) $request->input(
            'signature',
            ''
        );

        abort_if(
            $timestamp === ''
            || $token === ''
            || $signature === '',
            403,
            'Ongeldige Mailgun handtekening.'
        );

        $maxAge = max(
            60,
            (int) config(
                'live-chat-email.webhook_max_age_seconds',
                1800
            )
        );

        abort_if(
            abs(time() - (int) $timestamp) > $maxAge,
            403,
            'Mailgun webhook is te oud.'
        );

        $expected = hash_hmac(
            'sha256',
            $timestamp.$token,
            $key
        );

        abort_unless(
            hash_equals(
                $expected,
                $signature
            ),
            403,
            'Ongeldige Mailgun handtekening.'
        );

        return $token;
    }

    private function threadTokenFromRecipient(
        string $recipient
    ): string {
        $domain = preg_quote(
            strtolower(
                trim(
                    (string) config(
                        'live-chat-email.reply_domain'
                    )
                )
            ),
            '/'
        );

        $matched = preg_match(
            '/^reply\+([A-Za-z0-9]{32,80})@'
                .$domain.'$/i',
            $recipient,
            $matches
        );

        abort_unless(
            $matched === 1,
            404,
            'Onbekend reply-adres.'
        );

        return $matches[1];
    }

    private function assertAllowedSender(
        stdClass $conversation,
        string $sender
    ): void {
        if (
            ! config(
                'live-chat-email.strict_sender_match',
                true
            )
        ) {
            return;
        }

        $expected = $this->conversationEmail(
            $conversation
        );

        abort_unless(
            $expected
            && filter_var(
                $sender,
                FILTER_VALIDATE_EMAIL
            )
            && hash_equals(
                strtolower($expected),
                strtolower($sender)
            ),
            403,
            'Dit afzenderadres hoort niet bij dit gesprek.'
        );
    }

    private function incomingBody(
        Request $request
    ): string {
        $body = trim(
            (string) (
                $request->input('stripped-text')
                ?: $request->input('body-plain')
                ?: ''
            )
        );

        if ($body === '') {
            $html = (string) (
                $request->input('stripped-html')
                ?: $request->input('body-html')
                ?: ''
            );

            if ($html !== '') {
                $body = trim(
                    html_entity_decode(
                        strip_tags(
                            preg_replace(
                                '/<br\s*\/?>/i',
                                "\n",
                                $html
                            )
                        ),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    )
                );
            }
        }

        $max = max(
            500,
            (int) config(
                'live-chat-email.max_inbound_body_length',
                12000
            )
        );

        return Str::limit(
            preg_replace(
                "/\r\n?/",
                "\n",
                $body
            ) ?: '',
            $max,
            ''
        );
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function incomingFiles(
        Request $request
    ): array {
        $files = [];

        foreach ($request->allFiles() as $key => $value) {
            if (
                ! str_starts_with(
                    (string) $key,
                    'attachment-'
                )
            ) {
                continue;
            }

            if ($value instanceof UploadedFile) {
                $files[] = $value;
            }
        }

        return $files;
    }

    private function mailgunMessageId(
        Request $request
    ): ?string {
        foreach ([
            'Message-Id',
            'Message-ID',
            'message-id',
        ] as $field) {
            $value = trim(
                (string) $request->input(
                    $field,
                    ''
                )
            );

            if ($value !== '') {
                return Str::limit(
                    $value,
                    140,
                    ''
                );
            }
        }

        $rawHeaders = $request->input(
            'message-headers'
        );

        if (is_string($rawHeaders)) {
            $headers = json_decode(
                $rawHeaders,
                true
            );

            if (is_array($headers)) {
                foreach ($headers as $header) {
                    if (
                        is_array($header)
                        && count($header) >= 2
                        && strtolower(
                            (string) $header[0]
                        ) === 'message-id'
                    ) {
                        return Str::limit(
                            trim(
                                (string) $header[1]
                            ),
                            140,
                            ''
                        );
                    }
                }
            }
        }

        return null;
    }

    private function insertInboundMessage(
        stdClass $conversation,
        string $type,
        string $body,
        string $emailMessageId,
        ?UploadedFile $file
    ): bool {
        $path = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($file) {
            $maxBytes = max(
                1024,
                (int) config(
                    'live-chat-email.max_attachment_bytes',
                    20 * 1024 * 1024
                )
            );

            if (
                ! $file->isValid()
                || $file->getSize() <= 0
                || $file->getSize() > $maxBytes
            ) {
                return false;
            }

            $path = $file->store(
                'live-chat/email-attachments',
                'public'
            );

            $attachmentName = $file->getClientOriginalName()
                ?: basename($path);

            $attachmentMime = $file->getMimeType();
            $attachmentSize = $file->getSize();
        }

        DB::table('live_chat_messages')
            ->insert([
                'conversation_id' => $conversation->id,
                'client_id' => (string) Str::uuid(),
                'sender' => 'visitor',
                'sender_user_id' => $conversation->user_id
                    ? (int) $conversation->user_id
                    : null,
                'type' => $type,
                'body' => $body,
                'attachment_path' => $path,
                'attachment_name' => $attachmentName,
                'attachment_mime' => $attachmentMime,
                'attachment_size' => $attachmentSize,
                'source' => 'email',
                'email_message_id' => $emailMessageId,
                'email_in_reply_to' => null,
                'created_at' => now(),
            ]);

        return true;
    }
}
