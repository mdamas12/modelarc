<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Media */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbPath = is_array($this->variants) ? ($this->variants['thumb']['path'] ?? null) : null;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $this->url,
            'thumbnail_url' => $thumbPath
                ? url('/api/public/media/'.$this->uuid.'?variant=thumb')
                : $this->url,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'variants' => $this->variants,
            'type' => $this->type,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'sort_order' => $this->sort_order,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at,
        ];
    }
}
