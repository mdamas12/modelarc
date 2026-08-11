<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_id')->constrained('heroes')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('order')->default(1);
            $table->boolean('published')->default(true);
            $table->timestamps();

            $table->index(['hero_id', 'published', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_galleries');
    }
};
