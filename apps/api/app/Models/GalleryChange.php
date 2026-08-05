<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryChange extends Model
{
    protected $fillable = [
        'project_id',
        'before_media_id',
        'design_media_id',
        'after_media_id',
        'compare_with',
        'subcategory',
        'title',
        'description',
        'sort_order',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function beforeMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'before_media_id');
    }

    public function designMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'design_media_id');
    }

    public function afterMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'after_media_id');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function comparisonMedia(): ?Media
    {
        return $this->compare_with === 'design'
            ? $this->designMedia
            : $this->afterMedia;
    }

    public function compareLabel(): string
    {
        return $this->compare_with === 'design' ? 'Diseño' : 'Después';
    }
}
