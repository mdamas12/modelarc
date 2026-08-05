<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
            $table->index(['publication_status', 'sort_order']);
        });

        $ids = DB::table('projects')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->pluck('id');

        foreach ($ids as $index => $id) {
            DB::table('projects')->where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['publication_status', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
