<?php

namespace Tests\Feature;

use App\Jobs\SendContactLeadMailJob;
use App\Mail\ContactLeadMail;
use App\Models\Lead;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\CommercialMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_requires_budget_and_geo(): void
    {
        Queue::fake();

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
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('leads', [
            'email' => 'ana@example.com',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => 'high',
        ]);

        Queue::assertPushed(SendContactLeadMailJob::class);
    }

    public function test_public_contact_rejects_invalid_budget_range(): void
    {
        $response = $this->postJson('/api/public/contact', [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'budget_range' => '100-200M',
            'message' => 'Hola',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['budget_range']);
    }

    public function test_commercial_mail_recipients_use_env_without_legacy_fallbacks(): void
    {
        config(['mail.to.address' => 'modelarcca@gmail.com']);

        $recipients = CommercialMail::recipients();

        $this->assertSame(['modelarcca@gmail.com'], $recipients);
        $this->assertNotContains('info@modelarcve.com', $recipients);
        $this->assertNotContains('marcosdamas12@gmail.com', $recipients);
    }

    public function test_contact_lead_mail_goes_to_configured_recipient(): void
    {
        Mail::fake();
        config(['mail.to.address' => 'modelarcca@gmail.com']);
        config(['services.resend.key' => '']);

        $lead = Lead::query()->create([
            'name' => 'Carlos',
            'email' => 'carlos@example.com',
            'country' => 'España',
            'state' => 'Madrid',
            'city' => 'Madrid',
            'budget_range' => 'medium',
            'message' => 'Consulta',
            'status' => 'new',
            'source' => 'website',
        ]);

        (new SendContactLeadMailJob($lead->id))->handle(app(\App\Services\ContactMailNotifier::class));

        Mail::assertSent(ContactLeadMail::class, function (ContactLeadMail $mail) {
            return $mail->hasTo('modelarcca@gmail.com');
        });
    }

    public function test_admin_lead_filters_combine(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Lead::query()->create([
            'name' => 'Match',
            'email' => 'match@example.com',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'city' => 'Puerto Ordaz',
            'project_type' => 'Construcción',
            'budget_range' => 'high',
            'status' => 'new',
            'source' => 'website',
            'message' => 'A',
        ]);

        Lead::query()->create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'country' => 'España',
            'state' => 'Madrid',
            'city' => 'Madrid',
            'project_type' => 'Remodelación',
            'budget_range' => 'low',
            'status' => 'closed',
            'source' => 'website',
            'message' => 'B',
        ]);

        $response = $this->getJson('/api/admin/leads?'.http_build_query([
            'status' => 'new',
            'country' => 'Venezuela',
            'state' => 'Bolívar',
            'budget_range' => 'high',
            'project_type' => 'Construcción',
        ]));

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('match@example.com', $response->json('data.0.email'));
        $this->assertSame('Venezuela', $response->json('data.0.country'));
        $this->assertSame('high', $response->json('data.0.budget_range'));
    }

    public function test_home_exposes_whatsapp_settings_and_budget_ranges(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'whatsapp_phone'],
            ['value' => '584249171058'],
        );
        SiteSetting::query()->updateOrCreate(
            ['key' => 'whatsapp_message'],
            ['value' => 'Hola Modelarc'],
        );

        $response = $this->getJson('/api/public/home');

        $response->assertOk();
        $this->assertSame('584249171058', $response->json('data.settings.whatsapp_phone'));
        $this->assertSame('Hola Modelarc', $response->json('data.settings.whatsapp_message'));
        $this->assertNotEmpty($response->json('data.budget_ranges'));
        $this->assertSame('low', $response->json('data.budget_ranges.0.value'));
    }
}
