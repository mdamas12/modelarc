<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'variants',
        'type',
        'category',
        'subcategory',
        'sort_order',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Media $media): void {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_media')
            ->withPivot(['type', 'title', 'description', 'subcategory', 'sort_order', 'is_cover', 'is_published'])
            ->withTimestamps();
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        // Local disks are served via the API so CORS headers apply.
        // Photo Sphere Viewer fetches panoramas with fetch()/WebGL and
        // cannot use /storage/* static files from another origin.
        if (in_array($this->disk, ['public', 'local'], true)) {
            // Bust browser cache when web/thumb variants replace the heavy original payload.
            $version = is_array($this->variants) && ! empty($this->variants['web']['path']) ? 'w1' : 'o1';

            return url('/api/public/media/'.$this->uuid.'?v='.$version);
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
