<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourHotspot extends Model
{
    protected $fillable = [
        'tour_scene_id',
        'type',
        'title',
        'description',
        'yaw',
        'pitch',
        'icon',
        'target_scene_id',
        'media_id',
        'external_url',
        'configuration',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'yaw' => 'float',
            'pitch' => 'float',
            'configuration' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(TourScene::class, 'tour_scene_id');
    }

    public function targetScene(): BelongsTo
    {
        return $this->belongsTo(TourScene::class, 'target_scene_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
