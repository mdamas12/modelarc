<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GalleryChange */
class GalleryChangeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $comparison = $this->comparisonMedia();

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'before_media_id' => $this->before_media_id,
            'design_media_id' => $this->design_media_id,
            'after_media_id' => $this->after_media_id,
            'compare_with' => $this->compare_with,
            'compare_label' => $this->compareLabel(),
            'subcategory' => $this->subcategory,
            'title' => $this->title,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_featured' => $this->is_featured,
            'before_media' => new MediaResource($this->whenLoaded('beforeMedia')),
            'design_media' => new MediaResource($this->whenLoaded('designMedia')),
            'after_media' => new MediaResource($this->whenLoaded('afterMedia')),
            'comparison_media' => $comparison ? new MediaResource($comparison) : null,
            'comparison_image_url' => $comparison?->url,
            'before_image_url' => $this->whenLoaded('beforeMedia', fn () => $this->beforeMedia?->url),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
