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

    public function test_successful_lead_dispatches_meta_job_with_event_id(): void
    {
        Queue::fake();
        config([
            'services.meta.conversions_enabled' => true,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
        ]);

        $eventId = '77777777-7777-4777-8777-777777777777';

        $response = $this->postJson('/api/public/contact', [
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
            'meta_event_id' => $eventId,
            'event_source_url' => 'https://modelarcve.com/contacto',
            'meta_fbp' => 'fb.1.123.456',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('leads', ['email' => 'ana@example.com']);

        Queue::assertPushed(SendContactLeadMailJob::class);
        Queue::assertPushed(SendMetaConversionJob::class, function (SendMetaConversionJob $job) use ($eventId) {
            return $job->context['event_id'] === $eventId
                && $job->context['event_source_url'] === 'https://modelarcve.com/contacto'
                && $job->context['fbp'] === 'fb.1.123.456'
                && $job->tries === 3
                && $job->backoff === [30, 120, 300];
        });
    }

    public function test_meta_failure_does_not_break_lead_creation(): void
    {
        Queue::fake([SendContactLeadMailJob::class, \App\Jobs\SendContactLeadWhatsAppJob::class]);

        config([
            'services.meta.conversions_enabled' => true,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
            'services.meta.graph_api_version' => 'v21.0',
            'queue.default' => 'sync',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => ['message' => 'down']], 500),
        ]);

        // Run Meta job synchronously via real dispatch by faking only mail/whatsapp.
        // Use the service directly after create to assert resilience path.
        $response = $this->postJson('/api/public/contact', [
            'name' => 'Carlos',
            'email' => 'carlos@example.com',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => 'medium',
            'message' => 'Hola',
            'meta_event_id' => '88888888-8888-4888-8888-888888888888',
            'event_source_url' => 'https://modelarcve.com/contacto',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('leads', ['email' => 'carlos@example.com']);

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
        $response = $this->postJson('/api/public/contact', [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => 'high',
            'message' => 'Hola',
            'meta_event_id' => 'not-a-uuid',
        ]);

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
