<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_scene_id')->constrained('tour_scenes')->cascadeOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('yaw', 8, 3)->default(0);
            $table->decimal('pitch', 8, 3)->default(0);
            $table->string('icon')->nullable();
            $table->foreignId('target_scene_id')->nullable()->constrained('tour_scenes')->nullOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('external_url')->nullable();
            $table->json('configuration')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_hotspots');
    }
};
