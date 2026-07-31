<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Project */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'category' => $this->category,
            'location' => $this->location,
            'year' => $this->year,
            'status' => $this->status,
            'area' => $this->area,
            'duration' => $this->duration,
            'client_name' => $this->client_name,
            'is_featured' => $this->is_featured,
            'has_virtual_tour' => $this->has_virtual_tour,
            'publication_status' => $this->publication_status,
            'published_at' => $this->published_at,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'views_count' => $this->views_count,
            'project_type' => new ProjectTypeResource($this->whenLoaded('projectType')),
            'cover_media' => new MediaResource($this->whenLoaded('coverMedia')),
            'project_media' => ProjectMediaResource::collection($this->whenLoaded('projectMedia')),
            'virtual_tour' => new VirtualTourResource($this->whenLoaded('virtualTour')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
