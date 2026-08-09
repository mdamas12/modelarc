<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('we_are_teams', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('title')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();

            $table->index(['published', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('we_are_teams');
    }
};
