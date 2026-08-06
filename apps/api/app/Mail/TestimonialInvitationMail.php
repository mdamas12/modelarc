<?php

namespace App\Mail;

use App\Models\TestimonialInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestimonialInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TestimonialInvitation $invitation) {}

    public function envelope(): Envelope
    {
        $projectName = $this->invitation->project?->title ?? 'tu proyecto';

        return new Envelope(
            subject: "Tu opinión sobre {$projectName} — Modelarc",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.testimonial-invitation-html',
            with: [
                'clientName' => $this->invitation->client_name,
                'projectName' => $this->invitation->project?->title ?? 'tu proyecto',
                'url' => $this->invitation->publicUrl(),
            ],
        );
    }
}
