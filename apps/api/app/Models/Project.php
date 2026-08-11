<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'project_type_id',
        'category_id',
        'subcategory_id',
        'category',
        'location',
        'year',
        'status',
        'area',
        'duration',
        'client_name',
        'cover_media_id',
        'is_featured',
        'sort_order',
        'has_virtual_tour',
        'publication_status',
        'published_at',
        'seo_title',
        'seo_description',
        'views_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'has_virtual_tour' => 'boolean',
            'published_at' => 'datetime',
            'year' => 'integer',
            'views_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    /**
     * Named "categoryRef" (not "category") to avoid clashing with the legacy
     * `category` string attribute kept for backward compatibility.
     */
    public function categoryRef(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategoryRef(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'project_media')
            ->withPivot(['id', 'type', 'title', 'description', 'sort_order', 'is_cover'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function projectMedia(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('sort_order');
    }

    public function galleryChanges(): HasMany
    {
        return $this->hasMany(GalleryChange::class)->orderBy('sort_order');
    }

    public function virtualTours(): HasMany
    {
        return $this->hasMany(VirtualTour::class);
    }

    public function virtualTour(): HasOne
    {
        return $this->hasOne(VirtualTour::class)->latestOfMany();
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
