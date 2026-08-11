<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = [
        'client_name',
        'client_photo_media_id',
        'project_id',
        'project_label',
        'quote',
        'rating',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function clientPhoto(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'client_photo_media_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
