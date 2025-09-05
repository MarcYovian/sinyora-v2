<?php

namespace App\Mail;

use App\Enums\EventApprovalStatus;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $guest;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Event $event,
        public ?string $reason = null
    ) {
        $this->guest = $this->event->creator;
        $this->onQueue('notifications');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->event->status === EventApprovalStatus::APPROVED
            ? 'Selamat! Proposal Kegiatan Anda Disetujui'
            : 'Informasi Proposal Kegiatan Anda';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->event->status === EventApprovalStatus::APPROVED
            ? 'emails.events.approved'
            : 'emails.events.rejected';

        return new Content(
            markdown: $view,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
