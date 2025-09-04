<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AskForReviewEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reviewUrl;
    public string $displayName;

    public function __construct(string $reviewUrl, string $displayName = 'there')
    {
        $this->reviewUrl   = $reviewUrl;
        $this->displayName = $displayName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ask For Review Email');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ask-for-review');
    }

    public function attachments(): array { return []; }
}
