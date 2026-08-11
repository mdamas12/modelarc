<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Hero */
class HeroResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text_1' => $this->text_1,
            'text_2' => $this->text_2,
            'text_3' => $this->text_3,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
