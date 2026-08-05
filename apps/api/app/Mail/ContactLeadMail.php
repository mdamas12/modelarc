<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        $name = trim((string) $this->lead->name) ?: 'Sin nombre';

        return new Envelope(
            subject: "Nueva solicitud de contacto — {$name}",
            replyTo: [
                new Address(
                    (string) $this->lead->email,
                    $name,
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-lead',
            with: [
                'lead' => $this->lead,
                'adminUrl' => rtrim((string) config('app.admin_url', env('ADMIN_URL', '')), '/'),
                'receivedAt' => optional($this->lead->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
            ],
        );
    }
}
