<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\TourScene */
class TourSceneResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'virtual_tour_id' => $this->virtual_tour_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'initial_yaw' => $this->initial_yaw,
            'initial_pitch' => $this->initial_pitch,
            'initial_zoom' => $this->initial_zoom,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'panorama_media' => new MediaResource($this->whenLoaded('panoramaMedia')),
            'thumbnail_media' => new MediaResource($this->whenLoaded('thumbnailMedia')),
            'hotspots' => TourHotspotResource::collection($this->whenLoaded('hotspots')),
        ];
    }
}
