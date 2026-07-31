<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_tour_id')->constrained('virtual_tours')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('panorama_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('initial_yaw', 8, 3)->default(0);
            $table->decimal('initial_pitch', 8, 3)->default(0);
            $table->decimal('initial_zoom', 8, 3)->default(75);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['virtual_tour_id', 'slug']);
        });

        Schema::table('virtual_tours', function (Blueprint $table) {
            $table->foreign('initial_scene_id')
                ->references('id')
                ->on('tour_scenes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('virtual_tours', function (Blueprint $table) {
            $table->dropForeign(['initial_scene_id']);
        });

        Schema::dropIfExists('tour_scenes');
    }
};
