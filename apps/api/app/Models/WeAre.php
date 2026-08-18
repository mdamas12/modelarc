<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeAre extends Model
{
    protected $table = 'we_are';

    protected $fillable = [
        'title',
        'titulo_hero',
        'mensaje_hero',
        'description',
        'vision',
        'mission',
        'values',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Quiénes somos',
                'titulo_hero' => 'Arquitectura con propósito',
                'mensaje_hero' => 'Nosotros',
                'description' => null,
                'vision' => null,
                'mission' => null,
                'values' => null,
            ]
        );
    }
}
