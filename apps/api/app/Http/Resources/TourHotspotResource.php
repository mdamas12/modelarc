<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\TourHotspot */
class TourHotspotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'yaw' => $this->yaw,
            'pitch' => $this->pitch,
            'icon' => $this->icon,
            'target_scene_id' => $this->target_scene_id,
            'external_url' => $this->external_url,
            'configuration' => $this->configuration,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'media' => new MediaResource($this->whenLoaded('media')),
            'target_scene' => new TourSceneResource($this->whenLoaded('targetScene')),
        ];
    }
}
