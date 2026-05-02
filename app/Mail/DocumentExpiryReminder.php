<?php

namespace App\Mail;

use App\Models\VesselDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentExpiryReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public VesselDocument $document,
        public int $thresholdDays,
        public string $subjectLine,
        public string $bodyText,
    ) {
        $this->document->loadMissing(['vessel.branch', 'documentType']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-expiry-reminder',
            with: [
                'document' => $this->document,
                'thresholdDays' => $this->thresholdDays,
                'bodyText' => $this->bodyText,
            ],
        );
    }
}
