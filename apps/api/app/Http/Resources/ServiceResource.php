<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Service */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'summary' => $this->summary,
            'description' => $this->description,
            'features' => $this->features,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'image' => new MediaResource($this->whenLoaded('image')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
