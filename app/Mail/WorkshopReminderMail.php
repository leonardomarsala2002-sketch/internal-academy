<?php

namespace App\Mail;

use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkshopReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly Workshop $workshop,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Internal Academy Reminder: '.$this->workshop->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.workshops.reminder',
            with: [
                'recipientName' => $this->recipientName,
                'workshop' => $this->workshop,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
