<?php

namespace Tests\Feature;

use App\Jobs\SendContactLeadMailJob;
use App\Jobs\SendMetaConversionJob;
use App\Models\Lead;
use App\Services\MetaConversionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MetaLeadConversionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function leadPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'phone' => '+584241112233',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'project_type' => 'Diseño arquitectónico',
            'budget_range' => 'high',
            'message' => 'Necesito un presupuesto',
            'source' => 'website',
            'marketing_consent' => true,
            'meta_event_id' => '77777777-7777-4777-8777-777777777777',
            'event_source_url' => 'https://modelarcve.com/contacto',
            'meta_fbp' => 'fb.1.123.456',
        ], $overrides);
    }

    private function enableCapi(): void
    {
        config([
            'services.meta.conversions_enabled' => true,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
            'services.meta.graph_api_version' => 'v21.0',
        ]);
    }

    public function test_capi_enabled_marketing_false_with_forged_event_id_does_not_dispatch(): void
    {
        Queue::fake();
        $this->enableCapi();

        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'marketing_consent' => false,
            'meta_event_id' => 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee',
            'meta_fbp' => 'fb.1.forged',
            'meta_fbc' => 'fb.1.forged.fbc',
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('leads', ['email' => 'ana@example.com']);
        Queue::assertNotPushed(SendMetaConversionJob::class);
        Queue::assertPushed(SendContactLeadMailJob::class);
    }

    public function test_capi_enabled_marketing_true_with_event_id_dispatches(): void
    {
        Queue::fake();
        $this->enableCapi();

        $eventId = '77777777-7777-4777-8777-777777777777';

        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'meta_event_id' => $eventId,
        ]));

        $response->assertCreated();
        Queue::assertPushed(SendMetaConversionJob::class, function (SendMetaConversionJob $job) use ($eventId) {
            return $job->context['event_id'] === $eventId
                && $job->context['event_source_url'] === 'https://modelarcve.com/contacto'
                && $job->context['fbp'] === 'fb.1.123.456'
                && $job->tries === 3
                && $job->backoff === [30, 120, 300];
        });
    }

    public function test_capi_enabled_marketing_true_without_event_id_does_not_dispatch(): void
    {
        Queue::fake();
        $this->enableCapi();

        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'meta_event_id' => null,
            'meta_fbp' => null,
            'meta_fbc' => null,
        ]));

        $response->assertCreated();
        Queue::assertNotPushed(SendMetaConversionJob::class);
    }

    public function test_capi_disabled_marketing_true_does_not_dispatch(): void
    {
        Queue::fake();
        config([
            'services.meta.conversions_enabled' => false,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
        ]);

        $response = $this->postJson('/api/public/contact', $this->leadPayload());

        $response->assertCreated();
        Queue::assertNotPushed(SendMetaConversionJob::class);
    }

    public function test_marketing_false_lead_works_without_meta(): void
    {
        Queue::fake();
        $this->enableCapi();

        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'marketing_consent' => false,
            'meta_event_id' => null,
            'meta_fbp' => null,
            'meta_fbc' => null,
            'event_source_url' => null,
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('leads', [
            'email' => 'ana@example.com',
            'budget_range' => 'high',
        ]);
        Queue::assertNotPushed(SendMetaConversionJob::class);
    }

    public function test_invalid_marketing_consent_is_rejected(): void
    {
        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'marketing_consent' => 'yes-please',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['marketing_consent']);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_meta_failure_does_not_break_lead_creation(): void
    {
        Queue::fake([SendContactLeadMailJob::class, \App\Jobs\SendContactLeadWhatsAppJob::class]);
        $this->enableCapi();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => ['message' => 'down']], 500),
        ]);

        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'name' => 'Carlos',
            'email' => 'carlos@example.com',
            'meta_event_id' => '88888888-8888-4888-8888-888888888888',
        ]));

        $response->assertCreated();

        $lead = Lead::query()->where('email', 'carlos@example.com')->firstOrFail();

        try {
            app(MetaConversionsService::class)->sendLead($lead, [
                'event_id' => '88888888-8888-4888-8888-888888888888',
                'event_source_url' => 'https://modelarcve.com/contacto',
            ]);
            $this->fail('Expected Meta failure');
        } catch (\Throwable) {
            // expected
        }

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'email' => 'carlos@example.com',
        ]);
    }

    public function test_invalid_meta_event_id_is_rejected(): void
    {
        $response = $this->postJson('/api/public/contact', $this->leadPayload([
            'meta_event_id' => 'not-a-uuid',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['meta_event_id']);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_job_is_serializable(): void
    {
        $job = new SendMetaConversionJob(42, [
            'event_id' => '99999999-9999-4999-8999-999999999999',
            'event_source_url' => 'https://modelarcve.com/contacto',
            'client_ip_address' => '203.0.113.1',
            'client_user_agent' => 'TestAgent',
        ]);

        $serialized = serialize($job);
        $restored = unserialize($serialized);

        $this->assertInstanceOf(SendMetaConversionJob::class, $restored);
        $this->assertSame(42, $restored->leadId);
        $this->assertSame('99999999-9999-4999-8999-999999999999', $restored->context['event_id']);
    }
}
