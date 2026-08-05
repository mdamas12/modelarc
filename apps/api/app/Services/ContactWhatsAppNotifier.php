<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactWhatsAppNotifier
{
    public function send(Lead $lead): void
    {
        if (! (bool) config('services.callmebot.enabled', false)) {
            return;
        }

        $phone = preg_replace('/\D+/', '', (string) config('services.callmebot.phone', '')) ?? '';
        $apiKey = trim((string) config('services.callmebot.apikey', ''));

        if ($phone === '' || $apiKey === '') {
            Log::warning('CallMeBot omitido: falta CALLMEBOT_PHONE o CALLMEBOT_APIKEY', [
                'lead_id' => $lead->id,
            ]);

            return;
        }

        $message = $this->buildMessage($lead);

        try {
            $response = Http::timeout(20)
                ->accept('text/html')
                ->get('https://api.callmebot.com/whatsapp.php', [
                    'source' => 'php',
                    'phone' => $phone,
                    'text' => $message,
                    'apikey' => $apiKey,
                ]);

            if ($response->failed()) {
                Log::error('CallMeBot rechazó el aviso de WhatsApp', [
                    'lead_id' => $lead->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException('CallMeBot WhatsApp failed: '.$response->body());
            }
        } catch (Throwable $e) {
            Log::error('No se pudo enviar aviso WhatsApp de contacto', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function buildMessage(Lead $lead): string
    {
        $messageBody = trim((string) $lead->message);
        if (mb_strlen($messageBody) > 800) {
            $messageBody = mb_substr($messageBody, 0, 797).'...';
        }

        $lines = [
            '*Web Solicitud*',
            'Nombre: '.(trim((string) $lead->name) ?: '—'),
            'Email: '.(trim((string) $lead->email) ?: '—'),
            'Teléfono: '.(trim((string) $lead->phone) ?: '—'),
            'Servicio: '.(trim((string) $lead->project_type) ?: '—'),
            'Mensaje: '.($messageBody !== '' ? $messageBody : '—'),
        ];

        return implode("\n", $lines);
    }
}
