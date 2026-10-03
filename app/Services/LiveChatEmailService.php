<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;
use Throwable;

class LiveChatEmailService
{
    public function __construct(
        private readonly LiveChatService $chat,
        private readonly GmailApiClient $gmail
    ) {
    }

    /**
     * @return array{
     *   conversation:array<string,mixed>,
     *   email_sent:bool,
     *   email_error:?string,
     *   inbound_ready:bool
     * }
     */
    public function enable(
        int $conversationId,
        string $email,
        int $adminId,
        ?string $subject = null,
        ?string $title = null
    ): array {
        $this->assertEnabled();
        $this->assertGmailConfigured();

        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(422, 'Het e-mailadres van de klant is ongeldig.');
        }

        $subject = $this->normaliseSubject($subject);
        $title = $this->normaliseTitle($title);

        $result = DB::transaction(
            function () use (
                $conversationId,
                $email,
                $subject,
                $title
            ): array {
                $conversation = DB::table('live_chat_conversations')
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

                $currentSubject = trim(
                    (string) ($conversation->email_subject ?? '')
                );

                $gmailThreadId = trim(
                    (string) ($conversation->gmail_thread_id ?? '')
                );

                $resolvedSubject = $subject
                    ?: $currentSubject
                    ?: $this->newSubject($conversationId);

                if (
                    $gmailThreadId !== ''
                    && $currentSubject !== ''
                    && ! hash_equals(
                        $this->normaliseComparableSubject($currentSubject),
                        $this->normaliseComparableSubject($resolvedSubject)
                    )
                ) {
                    abort(
                        422,
                        'Het e-mailonderwerp is vergrendeld zodra de Gmail-thread is gestart. Wijzig de zichtbare titel in plaats daarvan om dezelfde e-mailthread te behouden.'
                    );
                }

                $resolvedTitle = $title
                    ?: trim((string) ($conversation->email_title ?? ''))
                    ?: 'Mashal Support';

                DB::table('live_chat_conversations')
                    ->where('id', $conversationId)
                    ->update([
                        'delivery_channel' => 'email',
                        'contact_email' => $email,
                        'email_thread_token' => $token,
                        'email_subject' => $resolvedSubject,
                        'email_title' => $resolvedTitle,
                        'email_handoff_at' => $conversation->email_handoff_at
                            ?: now(),
                        'status' => $conversation->status === 'closed'
                            ? 'open'
                            : $conversation->status,
                        'updated_at' => now(),
                    ]);

                return [
                    'was_email' => $wasEmail,
                ];
            }
        );

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

            $delivery = $this->deliverAdminMessage(
                $conversationId,
                $clientId
            );
        }

        return [
            'conversation' => $this->conversationState($conversationId),
            'email_sent' => (bool) $delivery['sent'],
            'email_error' => $delivery['error'],
            'inbound_ready' => $this->gmailInboundReady(),
        ];
    }

    /**
     * @return array{conversation:array<string,mixed>}
     */
    public function disable(int $conversationId): array
    {
        abort_unless(
            DB::table('live_chat_conversations')
                ->where('id', $conversationId)
                ->exists(),
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
     * Stuurt een opgeslagen adminbericht via de Gmail HTTPS API.
     * Als er al een Gmail-thread bestaat, wordt het bericht daar expliciet
     * als reply aan toegevoegd.
     *
     * @return array{sent:bool,skipped:bool,error:?string}
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

        $targetEmail = $this->conversationEmail(
            $conversation
        );

        if (! $targetEmail) {
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
            $this->assertEnabled();
            $this->assertGmailConfigured();

            $threadId = trim(
                (string) ($conversation->gmail_thread_id ?? '')
            );

            $subject = trim(
                (string) ($conversation->email_subject ?? '')
            );

            if ($subject === '') {
                $subject = $this->newSubject($conversationId);
            }

            $inReplyTo = null;
            $references = null;

            if ($threadId !== '') {
                $context = $this->threadReplyContext(
                    $threadId,
                    $subject
                );

                $subject = $context['subject'];
                $inReplyTo = $context['in_reply_to'];
                $references = $context['references'];
            }

            $bodyText = $this->mailBodyForMessage(
                $message
            );

            $raw = $this->buildRawEmail(
                to: $targetEmail,
                toName: $conversation->user_name ?: null,
                subject: $subject,
                text: $bodyText
                    ."\n\nAntwoord gewoon op deze e-mail. Uw antwoord verschijnt in hetzelfde supportgesprek.",
                html: $this->mailHtml(
                    $conversationId,
                    $bodyText,
                    $conversation->user_name ?: null,
                    $subject,
                    trim((string) ($conversation->email_title ?? ''))
                        ?: 'Mashal Support'
                ),
                attachments: $this->mailAttachmentsForMessage(
                    $message
                ),
                inReplyTo: $inReplyTo,
                references: $references
            );

            $sent = $this->gmail->sendRaw(
                $raw,
                $threadId !== '' ? $threadId : null
            );

            $gmailMessageId = trim(
                (string) ($sent['id'] ?? '')
            );

            $sentThreadId = trim(
                (string) ($sent['threadId'] ?? $threadId)
            );

            if ($gmailMessageId === '' || $sentThreadId === '') {
                throw new RuntimeException(
                    'Gmail gaf geen geldig message-id/thread-id terug.'
                );
            }

            $sentMetadata = $this->gmail->getMessage(
                $gmailMessageId,
                'metadata'
            );

            $sentHeaders = $this->headersFromPayload(
                (array) ($sentMetadata['payload'] ?? [])
            );

            $rfcMessageId = trim(
                (string) ($sentHeaders['message-id'] ?? '')
            );

            $actualSubject = trim(
                (string) ($sentHeaders['subject'] ?? $subject)
            );

            DB::transaction(
                function () use (
                    $conversationId,
                    $message,
                    $sentThreadId,
                    $actualSubject,
                    $rfcMessageId,
                    $inReplyTo
                ): void {
                    DB::table('live_chat_conversations')
                        ->where('id', $conversationId)
                        ->update([
                            'gmail_thread_id' => $sentThreadId,
                            'email_subject' => $actualSubject,
                            'updated_at' => now(),
                        ]);

                    DB::table('live_chat_messages')
                        ->where('id', $message->id)
                        ->update([
                            'email_sent_at' => now(),
                            'email_delivery_error' => null,
                            'email_message_id' => $rfcMessageId !== ''
                                ? Str::limit(
                                    $rfcMessageId,
                                    190,
                                    ''
                                )
                                : ($message->email_message_id ?? null),
                            'email_in_reply_to' => $inReplyTo !== null
                                ? Str::limit(
                                    $inReplyTo,
                                    190,
                                    ''
                                )
                                : null,
                            'source' => $message->source ?? 'live',
                        ]);
                }
            );

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
                'Live-chat Gmail e-mail kon niet worden verstuurd.',
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
     * @param array{
     *   gmail_message_id:string,
     *   gmail_thread_id:string,
     *   recipient?:string,
     *   from:string,
     *   message_id:string,
     *   in_reply_to:?string,
     *   subject?:string,
     *   body:string,
     *   attachments?:array<int,array{name:string,mime:?string,content:string}>
     * } $mail
     * @return array{ok:bool,duplicate?:bool,inserted?:int,conversation_id?:int}
     */
    public function receiveGmailMessage(
        array $mail
    ): array {
        $this->assertEnabled();

        $gmailMessageId = trim(
            (string) ($mail['gmail_message_id'] ?? '')
        );

        $gmailThreadId = trim(
            (string) ($mail['gmail_thread_id'] ?? '')
        );

        $messageId = trim(
            (string) ($mail['message_id'] ?? '')
        );

        if ($messageId === '') {
            $messageId = $gmailMessageId !== ''
                ? 'gmail-api:'.$gmailMessageId
                : 'gmail-api:'.hash(
                    'sha256',
                    json_encode($mail)
                );
        }

        $messageId = Str::limit(
            $messageId,
            190,
            ''
        );

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

        $conversation = $this->conversationFromInboundMail(
            gmailThreadId: $gmailThreadId,
            subject: (string) ($mail['subject'] ?? ''),
            inReplyTo: (string) ($mail['in_reply_to'] ?? '')
        );

        abort_unless(
            $conversation,
            404,
            'Geen live-chatgesprek gevonden voor deze e-mailthread.'
        );

        if (
            ($conversation->delivery_channel ?? 'live')
            !== 'email'
        ) {
            abort(
                409,
                'Dit gesprek staat niet meer in e-mailmodus.'
            );
        }

        $sender = strtolower(
            trim((string) ($mail['from'] ?? ''))
        );

        $this->assertAllowedSender(
            $conversation,
            $sender
        );

        $body = $this->limitInboundBody(
            (string) ($mail['body'] ?? '')
        );

        $attachments = is_array(
            $mail['attachments'] ?? null
        )
            ? $mail['attachments']
            : [];

        $inReplyTo = trim(
            (string) ($mail['in_reply_to'] ?? '')
        );

        $inserted = DB::transaction(
            function () use (
                $conversation,
                $body,
                $attachments,
                $messageId,
                $inReplyTo,
                $gmailThreadId,
                $mail
            ): int {
                $count = 0;

                if ($body !== '') {
                    $this->insertInboundMessage(
                        conversation: $conversation,
                        type: 'text',
                        body: $body,
                        emailMessageId: $messageId,
                        emailInReplyTo: $inReplyTo !== ''
                            ? $inReplyTo
                            : null,
                        attachment: null
                    );

                    $count++;
                }

                foreach (
                    $attachments as $index => $attachment
                ) {
                    if (! is_array($attachment)) {
                        continue;
                    }

                    $attachmentId = Str::limit(
                        $messageId
                            .':attachment:'
                            .($index + 1),
                        190,
                        ''
                    );

                    if (
                        DB::table('live_chat_messages')
                            ->where(
                                'email_message_id',
                                $attachmentId
                            )
                            ->exists()
                    ) {
                        continue;
                    }

                    if (
                        $this->insertInboundMessage(
                            conversation: $conversation,
                            type: 'file',
                            body: '',
                            emailMessageId: $attachmentId,
                            emailInReplyTo: $inReplyTo !== ''
                                ? $inReplyTo
                                : null,
                            attachment: $attachment
                        )
                    ) {
                        $count++;
                    }
                }

                DB::table('live_chat_conversations')
                    ->where('id', $conversation->id)
                    ->update([
                        'delivery_channel' => 'email',
                        'gmail_thread_id' => $gmailThreadId !== ''
                            ? $gmailThreadId
                            : ($conversation->gmail_thread_id ?? null),
                        'email_subject' => trim(
                            (string) ($mail['subject'] ?? '')
                        ) !== ''
                            ? trim(
                                (string) ($mail['subject'] ?? '')
                            )
                            : ($conversation->email_subject ?? null),
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
            'conversation_id' => (int) $conversation->id,
        ];
    }

    /**
     * @return array<string,mixed>
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
            'email_subject' => $conversation->email_subject
                ?? $this->newSubject($conversationId),
            'email_title' => $conversation->email_title
                ?? 'Mashal Support',
            'gmail_thread_id' => $conversation->gmail_thread_id
                ?? null,
            'email_subject_locked' => trim(
                (string) ($conversation->gmail_thread_id ?? '')
            ) !== '',
            'gmail_connected' => $this->gmailInboundReady(),
        ];
    }

    public function gmailInboundReady(): bool
    {
        return $this->gmail->configured();
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

    private function assertGmailConfigured(): void
    {
        if (! $this->gmail->configured()) {
            abort(
                503,
                'Gmail API is nog niet gekoppeld. Zet LIVE_CHAT_GMAIL_CLIENT_ID en LIVE_CHAT_GMAIL_CLIENT_SECRET in Railway en open daarna /admin/live-chat/gmail/connect.'
            );
        }
    }

    private function conversationWithUser(
        int $conversationId
    ): ?stdClass {
        return DB::table(
            'live_chat_conversations as c'
        )
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'c.user_id'
            )
            ->where(
                'c.id',
                $conversationId
            )
            ->select(
                'c.*',
                'u.name as user_name',
                'u.email as user_email'
            )
            ->first();
    }

    private function conversationWithUserByThread(
        string $gmailThreadId
    ): ?stdClass {
        if ($gmailThreadId === '') {
            return null;
        }

        return DB::table(
            'live_chat_conversations as c'
        )
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'c.user_id'
            )
            ->where(
                'c.gmail_thread_id',
                $gmailThreadId
            )
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
        if ($threadToken === '') {
            return null;
        }

        return DB::table(
            'live_chat_conversations as c'
        )
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

    private function conversationFromInboundMail(
        string $gmailThreadId,
        string $subject,
        string $inReplyTo
    ): ?stdClass {
        $conversation = $this->conversationWithUserByThread(
            trim($gmailThreadId)
        );

        if ($conversation) {
            return $conversation;
        }

        if (
            preg_match(
                '/\[LC:([A-Za-z0-9]{32,80})\]/',
                $subject,
                $matches
            ) === 1
        ) {
            $conversation = $this->conversationWithUserByToken(
                $matches[1]
            );

            if ($conversation) {
                return $conversation;
            }
        }

        if (
            preg_match(
                '/gesprek\s*#(\d+)/i',
                $subject,
                $matches
            ) === 1
        ) {
            $conversation = $this->conversationWithUser(
                (int) $matches[1]
            );

            if ($conversation) {
                return $conversation;
            }
        }

        $inReplyTo = Str::limit(
            trim($inReplyTo),
            190,
            ''
        );

        if ($inReplyTo !== '') {
            $conversationId = DB::table(
                'live_chat_messages'
            )
                ->where(
                    'email_message_id',
                    $inReplyTo
                )
                ->value('conversation_id');

            if ($conversationId) {
                return $this->conversationWithUser(
                    (int) $conversationId
                );
            }
        }

        return null;
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

    /**
     * @return array{
     *   subject:string,
     *   in_reply_to:?string,
     *   references:?string
     * }
     */
    private function threadReplyContext(
        string $threadId,
        string $fallbackSubject
    ): array {
        $thread = $this->gmail->getThread(
            $threadId,
            'metadata'
        );

        $messages = array_values(
            array_filter(
                (array) ($thread['messages'] ?? []),
                'is_array'
            )
        );

        if ($messages === []) {
            throw new RuntimeException(
                'De bestaande Gmail-thread kon niet worden gelezen.'
            );
        }

        $lastMessage = $messages[
            array_key_last($messages)
        ];

        $headers = $this->headersFromPayload(
            (array) ($lastMessage['payload'] ?? [])
        );

        $messageId = trim(
            (string) ($headers['message-id'] ?? '')
        );

        if ($messageId === '') {
            throw new RuntimeException(
                'Het laatste Gmail-bericht heeft geen Message-ID.'
            );
        }

        $subject = trim(
            (string) ($headers['subject'] ?? '')
        );

        if ($subject === '') {
            $subject = $fallbackSubject;
        }

        $existingReferences = trim(
            (string) ($headers['references'] ?? '')
        );

        $references = trim(
            $existingReferences.' '.$messageId
        );

        return [
            'subject' => $subject,
            'in_reply_to' => $messageId,
            'references' => $references,
        ];
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

    private function newSubject(
        int $conversationId
    ): string {
        $prefix = trim(
            (string) config(
                'live-chat-email.subject_prefix',
                'Mashal Support'
            )
        );

        return ($prefix ?: 'Mashal Support')
            .' · gesprek #'.$conversationId;
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
            'video' => 'Mashal Support heeft een video gestuurd.',
            default => 'Mashal Support heeft een nieuw bericht gestuurd.',
        };
    }

    /**
     * @return array<int,array{
     *   name:string,
     *   mime:string,
     *   content:string
     * }>
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

        $maxBytes = max(
            1024,
            (int) config(
                'live-chat-email.max_attachment_bytes',
                20 * 1024 * 1024
            )
        );

        $attachmentBytes = (int) Storage::disk('public')->size(
            $message->attachment_path
        );

        if ($attachmentBytes > $maxBytes) {
            throw new RuntimeException(
                'De bijlage is te groot om per e-mail te versturen.'
            );
        }

        $content = Storage::disk('public')->get(
            $message->attachment_path
        );

        return [[
            'name' => (string) (
                $message->attachment_name
                ?: basename(
                    $message->attachment_path
                )
            ),
            'mime' => (string) (
                $message->attachment_mime
                ?: 'application/octet-stream'
            ),
            'content' => $content,
        ]];
    }

    /**
     * @param array<int,array{name:string,mime:string,content:string}> $attachments
     */
    private function buildRawEmail(
        string $to,
        ?string $toName,
        string $subject,
        string $text,
        string $html,
        array $attachments,
        ?string $inReplyTo,
        ?string $references
    ): string {
        $fromEmail = $this->gmail->username();

        if (! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_USERNAME is ongeldig.'
            );
        }

        $headers = [
            'From: '.$this->mailboxHeader(
                $this->fromName(),
                $fromEmail
            ),
            'To: '.$this->mailboxHeader(
                $toName,
                $to
            ),
            'Subject: '.$this->encodeHeader($subject),
            'Date: '.date(DATE_RFC2822),
            'MIME-Version: 1.0',
        ];

        if ($inReplyTo !== null && trim($inReplyTo) !== '') {
            $headers[] = 'In-Reply-To: '.$this->safeHeader(
                $inReplyTo
            );
        }

        if ($references !== null && trim($references) !== '') {
            $headers[] = 'References: '.$this->safeHeader(
                $references
            );
        }

        $alternativeBoundary = 'alt_'.bin2hex(
            random_bytes(12)
        );

        $alternative = [
            '--'.$alternativeBoundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode($text),
            '--'.$alternativeBoundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            quoted_printable_encode($html),
            '--'.$alternativeBoundary.'--',
        ];

        if ($attachments === []) {
            $headers[] = 'Content-Type: multipart/alternative; boundary="'
                .$alternativeBoundary.'"';

            return implode("\r\n", $headers)
                ."\r\n\r\n"
                .implode("\r\n", $alternative)
                ."\r\n";
        }

        $mixedBoundary = 'mix_'.bin2hex(
            random_bytes(12)
        );

        $headers[] = 'Content-Type: multipart/mixed; boundary="'
            .$mixedBoundary.'"';

        $body = [
            '--'.$mixedBoundary,
            'Content-Type: multipart/alternative; boundary="'
                .$alternativeBoundary.'"',
            '',
            ...$alternative,
        ];

        foreach ($attachments as $attachment) {
            $name = $this->safeFilename(
                (string) ($attachment['name'] ?? 'bijlage')
            );

            $mime = $this->safeHeader(
                (string) (
                    $attachment['mime']
                    ?? 'application/octet-stream'
                )
            );

            $body[] = '--'.$mixedBoundary;
            $body[] = 'Content-Type: '.$mime.'; name="'.$name.'"';
            $body[] = 'Content-Transfer-Encoding: base64';
            $body[] = 'Content-Disposition: attachment; filename="'.$name.'"';
            $body[] = '';
            $body[] = rtrim(
                chunk_split(
                    base64_encode(
                        (string) ($attachment['content'] ?? '')
                    ),
                    76,
                    "\r\n"
                )
            );
        }

        $body[] = '--'.$mixedBoundary.'--';

        return implode("\r\n", $headers)
            ."\r\n\r\n"
            .implode("\r\n", $body)
            ."\r\n";
    }

    private function mailboxHeader(
        ?string $name,
        string $email
    ): string {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'Ongeldig e-mailadres: '.$email
            );
        }

        $name = trim((string) $name);

        return $name !== ''
            ? $this->encodeHeader($name).' <'.$email.'>'
            : '<'.$email.'>';
    }

    private function encodeHeader(
        string $value
    ): string {
        $value = $this->safeHeader($value);

        if (
            preg_match('/^[\x20-\x7E]*$/', $value)
            === 1
        ) {
            return $value;
        }

        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader(
                $value,
                'UTF-8',
                'B',
                "\r\n"
            );
        }

        return '=?UTF-8?B?'
            .base64_encode($value)
            .'?=';
    }

    private function safeHeader(
        string $value
    ): string {
        return trim(
            preg_replace(
                '/[\r\n]+/',
                ' ',
                $value
            ) ?: ''
        );
    }

    private function safeFilename(
        string $value
    ): string {
        $value = basename(
            str_replace(
                ["\r", "\n", '"'],
                '',
                $value
            )
        );

        return $value !== ''
            ? $value
            : 'bijlage';
    }

    private function fromName(): string
    {
        return trim(
            (string) config(
                'live-chat-email.from_name',
                'Mashal Support'
            )
        ) ?: 'Mashal Support';
    }

    private function mailHtml(
        int $conversationId,
        string $bodyText,
        ?string $customerName,
        string $emailSubject,
        string $emailTitle
    ): string {
        return view(
            'mail.thread',
            [
                'conversationId' => $conversationId,
                'bodyText' => $bodyText,
                'customerName' => $customerName,
                'emailSubject' => $emailSubject,
                'emailTitle' => $emailTitle,
            ]
        )->render();
    }

    private function normaliseSubject(?string $subject): ?string
    {
        if ($subject === null) {
            return null;
        }

        $subject = trim(
            preg_replace('/[\r\n]+/', ' ', $subject) ?: ''
        );

        if ($subject === '') {
            return null;
        }

        return Str::limit($subject, 180, '');
    }

    private function normaliseTitle(?string $title): ?string
    {
        if ($title === null) {
            return null;
        }

        $title = trim(
            preg_replace('/[\r\n]+/', ' ', $title) ?: ''
        );

        if ($title === '') {
            return null;
        }

        return Str::limit($title, 120, '');
    }

    private function normaliseComparableSubject(string $subject): string
    {
        $subject = preg_replace(
            '/^(?:(?:re|fw|fwd)\s*:\s*)+/i',
            '',
            trim($subject)
        ) ?: trim($subject);

        return mb_strtolower($subject, 'UTF-8');
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

    private function limitInboundBody(
        string $body
    ): string {
        $body = trim(
            preg_replace(
                "/\r\n?/",
                "\n",
                $body
            ) ?: ''
        );

        $max = max(
            500,
            (int) config(
                'live-chat-email.max_inbound_body_length',
                12000
            )
        );

        return Str::limit(
            $body,
            $max,
            ''
        );
    }

    /**
     * @param array{name?:string,mime?:?string,content?:string}|null $attachment
     */
    private function insertInboundMessage(
        stdClass $conversation,
        string $type,
        string $body,
        string $emailMessageId,
        ?string $emailInReplyTo,
        ?array $attachment
    ): bool {
        $path = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($attachment !== null) {
            $content = (string) (
                $attachment['content'] ?? ''
            );

            $maxBytes = max(
                1024,
                (int) config(
                    'live-chat-email.max_attachment_bytes',
                    20 * 1024 * 1024
                )
            );

            if (
                $content === ''
                || strlen($content) > $maxBytes
            ) {
                return false;
            }

            $attachmentName = $this->safeFilename(
                (string) (
                    $attachment['name']
                    ?? 'bijlage'
                )
            );

            $attachmentMime = trim(
                (string) (
                    $attachment['mime']
                    ?? 'application/octet-stream'
                )
            ) ?: 'application/octet-stream';

            $storedName = (string) Str::uuid()
                .'-'
                .preg_replace(
                    '/[^A-Za-z0-9._-]+/',
                    '-',
                    $attachmentName
                );

            $path = 'live-chat/email-attachments/'
                .$storedName;

            Storage::disk('public')->put(
                $path,
                $content
            );

            $attachmentSize = strlen($content);
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
                'email_in_reply_to' => $emailInReplyTo !== null
                    ? Str::limit(
                        $emailInReplyTo,
                        190,
                        ''
                    )
                    : null,
                'created_at' => now(),
            ]);

        return true;
    }
}
