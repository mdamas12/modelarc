<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Testimonial */
class TestimonialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'quote' => $this->quote,
            'rating' => $this->rating,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'client_photo' => new MediaResource($this->whenLoaded('clientPhoto')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'created_at' => $this->created_at,
        ];
    }
}
