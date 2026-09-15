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

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('testimonial_invitations', function (Blueprint $table) {
                $table->dropColumn('project_id');
            });
            Schema::table('testimonial_invitations', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            });
        } else {
            DB::statement('ALTER TABLE testimonial_invitations MODIFY project_id BIGINT UNSIGNED NULL');

            Schema::table('testimonial_invitations', function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            });
        }

        // Backfill label from linked project title for existing invitations.
        $rows = DB::table('testimonial_invitations')
            ->whereNotNull('project_id')
            ->where(function ($q) {
                $q->whereNull('project_label')->orWhere('project_label', '');
            })
            ->get(['id', 'project_id']);

        foreach ($rows as $row) {
            $title = DB::table('projects')->where('id', $row->project_id)->value('title');
            if ($title) {
                DB::table('testimonial_invitations')->where('id', $row->id)->update(['project_label' => $title]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('testimonial_invitations', function (Blueprint $table) {
                $table->dropColumn('project_id');
            });
            Schema::table('testimonial_invitations', function (Blueprint $table) {
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            });
        } else {
            DB::statement('ALTER TABLE testimonial_invitations MODIFY project_id BIGINT UNSIGNED NOT NULL');

            Schema::table('testimonial_invitations', function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            });
        }

        Schema::table('testimonial_invitations', function (Blueprint $table) {
            $table->dropColumn('project_label');
        });
    }
};
