<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hero extends Model
{
    protected $fillable = [
        'text_1',
        'text_2',
        'text_3',
    ];

    public function galleries(): HasMany
    {
        return $this->hasMany(HeroGallery::class);
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'text_1' => 'Arquitectura · Construcción · Remodelación',
                'text_2' => "Diseñamos espacios\nque transforman tu vida",
                'text_3' => 'Proyectos residenciales y comerciales con identidad, precisión técnica y una experiencia inmersiva en cada detalle.',
            ]
        );
    }
}
