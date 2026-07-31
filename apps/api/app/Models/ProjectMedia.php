<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMedia extends Model
{
    protected $table = 'project_media';

    protected $fillable = [
        'project_id',
        'media_id',
        'type',
        'title',
        'description',
        'subcategory',
        'sort_order',
        'is_cover',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
