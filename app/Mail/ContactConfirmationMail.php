<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Gegevens van het contactformulier.
     */
    public function __construct(
        public array $data
    ) {
    }

    /**
     * Onderwerp van de bevestigingsmail.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We hebben uw bericht ontvangen | SmartDesk',
        );
    }

    /**
     * Blade-view van de bevestigingsmail.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-confirmation',
            with: [
                'data' => $this->data,
            ],
        );
    }

    /**
     * Geen bijlagen.
     */
    public function attachments(): array
    {
        return [];
    }
}