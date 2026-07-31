<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type')->index();
            $table->string('subcategory')->nullable()->after('category')->index();
            $table->unsignedInteger('sort_order')->default(0)->after('subcategory')->index();
            $table->boolean('is_published')->default(true)->after('sort_order')->index();
        });

        Schema::table('project_media', function (Blueprint $table) {
            $table->string('subcategory')->nullable()->after('description')->index();
            $table->boolean('is_published')->default(true)->after('is_cover')->index();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['category', 'subcategory', 'sort_order', 'is_published']);
        });

        Schema::table('project_media', function (Blueprint $table) {
            $table->dropColumn(['subcategory', 'is_published']);
        });
    }
};
