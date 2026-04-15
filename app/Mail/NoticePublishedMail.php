<?php

namespace App\Mail;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoticePublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notice $notice;

    public User $recipient;

    public string $messageBody;

    public function __construct(Notice $notice, User $recipient)
    {
        $this->notice = $notice;
        $this->recipient = $recipient;
        $this->messageBody = trim((string) ($notice->email_body ?: $notice->content));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notice->email_subject ?: $this->notice->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notices.published',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
