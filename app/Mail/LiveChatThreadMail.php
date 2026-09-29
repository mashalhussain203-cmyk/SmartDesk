<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LiveChatThreadMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<int, array{disk:string,path:string,name:string,mime:string|null}> $files
     */
    public function __construct(
        public readonly int $conversationId,
        public readonly string $bodyText,
        public readonly string $replyAddress,
        public readonly ?string $customerName = null,
        public readonly array $files = [],
    ) {
    }

    public function envelope(): Envelope
    {
        $prefix = trim(
            (string) config(
                'live-chat-email.subject_prefix',
                'Mashal Support'
            )
        );

        return new Envelope(
            replyTo: [
                new Address(
                    $this->replyAddress,
                    $prefix ?: 'Mashal Support'
                ),
            ],
            subject: ($prefix ?: 'Mashal Support')
                .' · gesprek #'.$this->conversationId,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.live-chat.thread',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->files as $file) {
            if (
                empty($file['disk'])
                || empty($file['path'])
                || empty($file['name'])
            ) {
                continue;
            }

            $attachment = Attachment::fromStorageDisk(
                $file['disk'],
                $file['path']
            )->as($file['name']);

            if (! empty($file['mime'])) {
                $attachment = $attachment->withMime(
                    $file['mime']
                );
            }

            $attachments[] = $attachment;
        }

        return $attachments;
    }
}
