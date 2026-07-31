<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourScene extends Model
{
    protected $fillable = [
        'virtual_tour_id',
        'name',
        'slug',
        'panorama_media_id',
        'thumbnail_media_id',
        'description',
        'initial_yaw',
        'initial_pitch',
        'initial_zoom',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'initial_yaw' => 'float',
            'initial_pitch' => 'float',
            'initial_zoom' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function virtualTour(): BelongsTo
    {
        return $this->belongsTo(VirtualTour::class);
    }

    public function panoramaMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'panorama_media_id');
    }

    public function thumbnailMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }

    public function hotspots(): HasMany
    {
        return $this->hasMany(TourHotspot::class)->orderBy('sort_order');
    }
}
