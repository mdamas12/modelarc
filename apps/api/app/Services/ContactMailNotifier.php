<?php

namespace App\Services;

use App\Mail\ContactLeadMail;
use App\Models\Lead;
use App\Support\CommercialMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactMailNotifier
{
    public function send(Lead $lead): void
    {
        $recipients = CommercialMail::recipients();

        if ($recipients === []) {
            return;
        }

        $subject = 'Nueva solicitud de contacto — '.(trim((string) $lead->name) ?: 'Sin nombre');
        $html = view('emails.contact-lead', [
            'lead' => $lead,
            'adminUrl' => rtrim((string) config('app.admin_url', env('ADMIN_URL', '')), '/'),
            'receivedAt' => optional($lead->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
        ])->render();

        $resendKey = (string) config('services.resend.key', '');

        if ($resendKey !== '') {
            $this->sendViaResend($resendKey, $recipients, $lead, $subject, $html);

            return;
        }

        try {
            Mail::to($recipients)->sendNow(new ContactLeadMail($lead));
        } catch (Throwable $e) {
            Log::error('No se pudo enviar email de contacto por SMTP', [
                'lead_id' => $lead->id,
                'to' => $recipients,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  list<string>  $recipients
     */
    protected function sendViaResend(string $apiKey, array $recipients, Lead $lead, string $subject, string $html): void
    {
        $fromAddress = (string) config('mail.from.address', CommercialMail::DEFAULT_TO);
        $fromName = (string) config('mail.from.name', 'Modelarc');

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(20)
            ->post('https://api.resend.com/emails', [
                'from' => "{$fromName} <{$fromAddress}>",
                'to' => $recipients,
                'reply_to' => (string) $lead->email,
                'subject' => $subject,
                'html' => $html,
            ]);

        if ($response->failed()) {
            Log::error('Resend rechazó el email de contacto', [
                'lead_id' => $lead->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Resend email failed: '.$response->body());
        }
    }
}
