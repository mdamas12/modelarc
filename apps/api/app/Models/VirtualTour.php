<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualTour extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'description',
        'status',
        'initial_scene_id',
        'autorotate_enabled',
        'autorotate_speed',
        'show_compass',
        'show_scene_selector',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'autorotate_enabled' => 'boolean',
            'show_compass' => 'boolean',
            'show_scene_selector' => 'boolean',
            'autorotate_speed' => 'float',
            'published_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(TourScene::class)->orderBy('sort_order');
    }

    public function initialScene(): BelongsTo
    {
        return $this->belongsTo(TourScene::class, 'initial_scene_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
