<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'whatsapp_phone'],
            ['value' => '584249171058'],
        );

        SiteSetting::query()->updateOrCreate(
            ['key' => 'whatsapp_message'],
            ['value' => 'Hola Modelarc, me interesa solicitar información sobre un proyecto.'],
        );

        SiteSetting::query()->updateOrCreate(
            ['key' => 'contact_email'],
            ['value' => 'modelarcca@gmail.com'],
        );
    }

    public function down(): void
    {
        SiteSetting::query()->whereIn('key', ['whatsapp_phone', 'whatsapp_message'])->delete();
    }
};
