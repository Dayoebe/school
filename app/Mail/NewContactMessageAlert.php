<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContactMessageAlert extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;

    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    public function envelope(): Envelope
    {
        $schoolName = $this->contactMessage->school?->name ?: 'School';

        return new Envelope(
            subject: 'New Contact Message - ' . $schoolName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message.new-message-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
