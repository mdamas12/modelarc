<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('before_media_id')->constrained('media')->restrictOnDelete();
            $table->foreignId('design_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('after_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('compare_with', 20);
            $table->string('subcategory')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['project_id', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_changes');
    }
};
