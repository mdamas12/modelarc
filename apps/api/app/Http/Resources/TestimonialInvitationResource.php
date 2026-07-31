<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\TestimonialInvitation */
class TestimonialInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token' => $this->when($request->user() !== null, $this->token),
            'client_name' => $this->client_name,
            'client_email' => $this->when($request->user() !== null, $this->client_email),
            'status' => $this->status,
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', fn () => [
                'id' => $this->project?->id,
                'title' => $this->project?->title,
                'slug' => $this->project?->slug,
                'category' => $this->project?->category,
                'location' => $this->project?->location,
            ]),
            'testimonial_id' => $this->testimonial_id,
            'public_url' => $this->when($request->user() !== null, $this->publicUrl()),
            'sent_at' => $this->sent_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
        ];
    }
}
