<?php

namespace App\Services;

use App\Models\Lead;
use App\Support\MetaUserDataHasher;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MetaConversionsService
{
    public function isEnabled(): bool
    {
        $pixelId = (string) config('services.meta.pixel_id', '');
        $token = (string) config('services.meta.access_token', '');

        return (bool) config('services.meta.conversions_enabled', false)
            && $pixelId !== ''
            && $token !== '';
    }

    /**
     * Send a Lead conversion for an existing lead. Never throws to callers
     * outside Jobs — Jobs should catch HTTP failures for retries.
     *
     * @param  array{
     *     event_id: string,
     *     event_source_url?: ?string,
     *     client_ip_address?: ?string,
     *     client_user_agent?: ?string,
     *     fbp?: ?string,
     *     fbc?: ?string
     * }  $context
     */
    public function sendLead(Lead $lead, array $context): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $eventId = trim((string) ($context['event_id'] ?? ''));
        if ($eventId === '') {
            Log::warning('Meta CAPI Lead skipped: missing event_id', [
                'lead_id' => $lead->id,
            ]);

            return;
        }

        $payload = $this->buildLeadPayload($lead, $context);
        $this->postEvents($payload);
    }

    /**
     * @param  array{
     *     event_id: string,
     *     event_source_url?: ?string,
     *     client_ip_address?: ?string,
     *     client_user_agent?: ?string,
     *     fbp?: ?string,
     *     fbc?: ?string
     * }  $context
     * @return array<string, mixed>
     */
    public function buildLeadPayload(Lead $lead, array $context): array
    {
        [$firstName, $lastName] = MetaUserDataHasher::splitName($lead->name);

        $userData = array_filter([
            'em' => $this->wrapHash(MetaUserDataHasher::hashEmail($lead->email)),
            'ph' => $this->wrapHash(MetaUserDataHasher::hashPhone($lead->phone)),
            'fn' => $this->wrapHash(MetaUserDataHasher::hashNamePart($firstName)),
            'ln' => $this->wrapHash(MetaUserDataHasher::hashNamePart($lastName)),
            'ct' => $this->wrapHash(MetaUserDataHasher::hashCity($lead->city)),
            'st' => $this->wrapHash(MetaUserDataHasher::hashState($lead->state)),
            'country' => $this->wrapHash(MetaUserDataHasher::hashCountry($lead->country)),
            'client_ip_address' => $this->nullableString($context['client_ip_address'] ?? null),
            'client_user_agent' => $this->nullableString($context['client_user_agent'] ?? null),
            'fbp' => $this->nullableString($context['fbp'] ?? null),
            'fbc' => $this->nullableString($context['fbc'] ?? null),
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);

        $customData = array_filter([
            'content_name' => 'contact_form',
            'lead_type' => 'website_contact',
            'content_category' => $this->nullableString($lead->project_type),
            'budget_range' => $this->nullableString($lead->budget_range),
            'country' => $this->nullableString($lead->country),
        ], fn ($value) => $value !== null && $value !== '');

        $event = array_filter([
            'event_name' => 'Lead',
            'event_time' => time(),
            'event_id' => (string) $context['event_id'],
            'action_source' => 'website',
            'event_source_url' => $this->nullableString($context['event_source_url'] ?? null),
            'user_data' => $userData,
            'custom_data' => $customData === [] ? null : $customData,
        ], fn ($value) => $value !== null && $value !== '');

        $body = [
            'data' => [$event],
        ];

        $testCode = trim((string) config('services.meta.test_event_code', ''));
        if ($testCode !== '') {
            $body['test_event_code'] = $testCode;
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function postEvents(array $payload): Response
    {
        $pixelId = (string) config('services.meta.pixel_id');
        $version = (string) config('services.meta.graph_api_version', 'v21.0');
        $token = (string) config('services.meta.access_token');

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/events',
            rawurlencode($version),
            rawurlencode($pixelId),
        );

        try {
            $response = Http::asJson()
                ->timeout(15)
                ->acceptJson()
                ->post($url, array_merge($payload, [
                    'access_token' => $token,
                ]));
        } catch (Throwable $e) {
            Log::error('Meta CAPI request failed', [
                'error' => $e->getMessage(),
                'pixel_id' => $pixelId,
            ]);

            throw $e;
        }

        if ($response->failed()) {
            Log::error('Meta CAPI returned error', [
                'status' => $response->status(),
                'body' => $this->sanitizeResponseBody($response->body()),
                'pixel_id' => $pixelId,
            ]);

            throw new RuntimeException(
                'Meta Conversions API request failed with HTTP '.$response->status()
            );
        }

        return $response;
    }

    /**
     * @return list<string>|null
     */
    private function wrapHash(?string $hash): ?array
    {
        return $hash === null || $hash === '' ? null : [$hash];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function sanitizeResponseBody(string $body): string
    {
        $token = (string) config('services.meta.access_token', '');
        if ($token !== '') {
            $body = str_replace($token, '[redacted]', $body);
        }

        return mb_substr($body, 0, 500);
    }
}
