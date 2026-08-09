<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\WeAre */
class WeAreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'vision' => $this->vision,
            'mission' => $this->mission,
            'values' => $this->values,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
