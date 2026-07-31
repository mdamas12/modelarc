<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ProjectMedia */
class ProjectMediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'media_id' => $this->media_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'subcategory' => $this->subcategory,
            'sort_order' => $this->sort_order,
            'is_cover' => $this->is_cover,
            'is_published' => $this->is_published,
            'media' => new MediaResource($this->whenLoaded('media')),
        ];
    }
}
