<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHotspotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['scene', 'info', 'media', 'link'])],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'yaw' => ['required', 'numeric'],
            'pitch' => ['required', 'numeric'],
            'icon' => ['nullable', 'string', 'max:100'],
            'target_scene_id' => ['nullable', 'exists:tour_scenes,id'],
            'media_id' => ['nullable', 'exists:media,id'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'configuration' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
