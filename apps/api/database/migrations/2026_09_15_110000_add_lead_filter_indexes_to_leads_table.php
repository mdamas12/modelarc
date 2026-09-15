<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index('country');
            $table->index('state');
            $table->index('budget_range');
            $table->index('project_type');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropIndex(['state']);
            $table->dropIndex(['budget_range']);
            $table->dropIndex(['project_type']);
        });
    }
};
