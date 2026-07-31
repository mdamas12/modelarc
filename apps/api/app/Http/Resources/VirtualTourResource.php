<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\VirtualTour */
class VirtualTourResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'initial_scene_id' => $this->initial_scene_id,
            'autorotate_enabled' => $this->autorotate_enabled,
            'autorotate_speed' => $this->autorotate_speed,
            'show_compass' => $this->show_compass,
            'show_scene_selector' => $this->show_scene_selector,
            'published_at' => $this->published_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'initial_scene' => new TourSceneResource($this->whenLoaded('initialScene')),
            'scenes' => TourSceneResource::collection($this->whenLoaded('scenes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
