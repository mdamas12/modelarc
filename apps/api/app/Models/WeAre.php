<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeAre extends Model
{
    protected $table = 'we_are';

    protected $fillable = [
        'title',
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
                'description' => null,
                'vision' => null,
                'mission' => null,
                'values' => null,
            ]
        );
    }
}
