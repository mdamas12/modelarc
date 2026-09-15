<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Services\MetaConversionsService;
use App\Support\MetaUserDataHasher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MetaConversionsServiceTest extends TestCase
{
    public function test_disabled_integration_does_not_send(): void
    {
        config([
            'services.meta.conversions_enabled' => false,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
        ]);

        Http::fake();

        $lead = new Lead([
            'name' => 'Ana Pérez',
            'email' => 'Ana.Perez@Example.com',
            'phone' => '+58 424-111-2233',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => 'high',
            'project_type' => 'Diseño arquitectónico',
        ]);
        $lead->id = 1;

        app(MetaConversionsService::class)->sendLead($lead, [
            'event_id' => '11111111-1111-4111-8111-111111111111',
        ]);

        Http::assertNothingSent();
    }

    public function test_lead_payload_structure_and_hashing(): void
    {
        config([
            'services.meta.conversions_enabled' => true,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
            'services.meta.test_event_code' => '',
            'services.meta.graph_api_version' => 'v21.0',
        ]);

        $lead = new Lead([
            'name' => 'Ana Pérez',
            'email' => 'Ana.Perez@Example.com',
            'phone' => '+58 424-111-2233',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => 'high',
            'project_type' => 'Diseño arquitectónico',
            'message' => 'No debe enviarse',
        ]);
        $lead->id = 9;

        $payload = app(MetaConversionsService::class)->buildLeadPayload($lead, [
            'event_id' => '22222222-2222-4222-8222-222222222222',
            'event_source_url' => 'https://modelarcve.com/contacto',
            'client_ip_address' => '203.0.113.10',
            'client_user_agent' => 'PHPUnit',
        ]);

        $this->assertArrayHasKey('data', $payload);
        $this->assertCount(1, $payload['data']);
        $this->assertArrayNotHasKey('test_event_code', $payload);

        $event = $payload['data'][0];
        $this->assertSame('Lead', $event['event_name']);
        $this->assertSame('22222222-2222-4222-8222-222222222222', $event['event_id']);
        $this->assertSame('website', $event['action_source']);
        $this->assertSame('https://modelarcve.com/contacto', $event['event_source_url']);
        $this->assertIsInt($event['event_time']);
        $this->assertSame('1053858764178204', (string) config('services.meta.pixel_id'));

        $expectedEmail = MetaUserDataHasher::hashEmail('Ana.Perez@Example.com');
        $expectedPhone = MetaUserDataHasher::hashPhone('+58 424-111-2233');
        $this->assertSame([$expectedEmail], $event['user_data']['em']);
        $this->assertSame([$expectedPhone], $event['user_data']['ph']);
        $this->assertArrayHasKey('fn', $event['user_data']);
        $this->assertArrayHasKey('ln', $event['user_data']);
        $this->assertSame('203.0.113.10', $event['user_data']['client_ip_address']);
        $this->assertSame('PHPUnit', $event['user_data']['client_user_agent']);

        $this->assertSame('high', $event['custom_data']['budget_range']);
        $this->assertSame('Diseño arquitectónico', $event['custom_data']['content_category']);
        $this->assertArrayNotHasKey('message', $event['custom_data'] ?? []);
        $this->assertStringNotContainsString('Ana.Perez@Example.com', json_encode($payload));
        $this->assertStringNotContainsString('+58 424-111-2233', json_encode($payload));
    }

    public function test_empty_optional_fields_are_omitted(): void
    {
        $lead = new Lead([
            'name' => 'Solo',
            'email' => 'solo@example.com',
            'phone' => null,
            'country' => 'Atlantis', // unmapped → omit country hash
            'state' => '',
            'city' => '',
            'budget_range' => null,
            'project_type' => null,
        ]);

        $payload = app(MetaConversionsService::class)->buildLeadPayload($lead, [
            'event_id' => '33333333-3333-4333-8333-333333333333',
        ]);

        $userData = $payload['data'][0]['user_data'];
        $this->assertArrayHasKey('em', $userData);
        $this->assertArrayHasKey('fn', $userData);
        $this->assertArrayNotHasKey('ph', $userData);
        $this->assertArrayNotHasKey('ln', $userData);
        $this->assertArrayNotHasKey('ct', $userData);
        $this->assertArrayNotHasKey('st', $userData);
        $this->assertArrayNotHasKey('country', $userData);
    }

    public function test_test_event_code_included_when_configured(): void
    {
        config([
            'services.meta.test_event_code' => 'TEST12345',
        ]);

        $lead = new Lead([
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        $payload = app(MetaConversionsService::class)->buildLeadPayload($lead, [
            'event_id' => '44444444-4444-4444-8444-444444444444',
        ]);

        $this->assertSame('TEST12345', $payload['test_event_code']);
    }

    public function test_post_events_hits_correct_endpoint(): void
    {
        config([
            'services.meta.conversions_enabled' => true,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'test-token-not-real',
            'services.meta.graph_api_version' => 'v21.0',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $lead = new Lead([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => 'medium',
        ]);
        $lead->id = 3;

        app(MetaConversionsService::class)->sendLead($lead, [
            'event_id' => '55555555-5555-4555-8555-555555555555',
            'event_source_url' => 'https://modelarcve.com/contacto',
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v21.0/1053858764178204/events'
                && $request['access_token'] === 'test-token-not-real'
                && $request['data'][0]['event_name'] === 'Lead'
                && $request['data'][0]['event_id'] === '55555555-5555-4555-8555-555555555555'
                && $request['data'][0]['action_source'] === 'website';
        });
    }

    public function test_failed_meta_response_does_not_log_token(): void
    {
        config([
            'services.meta.conversions_enabled' => true,
            'services.meta.pixel_id' => '1053858764178204',
            'services.meta.access_token' => 'super-secret-token-xyz',
            'services.meta.graph_api_version' => 'v21.0',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(
                ['error' => ['message' => 'bad token super-secret-token-xyz']],
                400
            ),
        ]);

        Log::spy();

        $lead = new Lead([
            'name' => 'Ana',
            'email' => 'ana@example.com',
        ]);
        $lead->id = 4;

        try {
            app(MetaConversionsService::class)->sendLead($lead, [
                'event_id' => '66666666-6666-4666-8666-666666666666',
            ]);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('400', $e->getMessage());
        }

        Log::shouldHaveReceived('error')->withArgs(function (string $message, array $context) {
            $encoded = json_encode($context) ?: '';

            return $message === 'Meta CAPI returned error'
                && ! str_contains($encoded, 'super-secret-token-xyz');
        });
    }
}
