<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('we_are', function (Blueprint $table) {
            $table->string('titulo_hero')->nullable()->after('title');
            $table->text('mensaje_hero')->nullable()->after('titulo_hero');
        });

        DB::table('we_are')->where('id', 1)->update([
            'titulo_hero' => 'Arquitectura con propósito',
            'mensaje_hero' => 'Nosotros',
        ]);
    }

    public function down(): void
    {
        Schema::table('we_are', function (Blueprint $table) {
            $table->dropColumn(['titulo_hero', 'mensaje_hero']);
        });
    }
};
