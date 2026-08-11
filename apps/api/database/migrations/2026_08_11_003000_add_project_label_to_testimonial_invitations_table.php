<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->string('project_label')->nullable()->after('project_id');
        });

        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        DB::statement('ALTER TABLE testimonial_invitations MODIFY project_id BIGINT UNSIGNED NULL');

        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });

        // Backfill label from linked project title for existing invitations.
        DB::statement(
            'UPDATE testimonial_invitations ti
             INNER JOIN projects p ON p.id = ti.project_id
             SET ti.project_label = p.title
             WHERE ti.project_label IS NULL OR ti.project_label = \'\''
        );
    }

    public function down(): void
    {
        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        DB::statement('ALTER TABLE testimonial_invitations MODIFY project_id BIGINT UNSIGNED NOT NULL');

        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->dropColumn('project_label');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }
};
