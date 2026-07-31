<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSceneRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'panorama_media_id' => ['nullable', 'exists:media,id'],
            'thumbnail_media_id' => ['nullable', 'exists:media,id'],
            'description' => ['nullable', 'string'],
            'initial_yaw' => ['nullable', 'numeric'],
            'initial_pitch' => ['nullable', 'numeric'],
            'initial_zoom' => ['nullable', 'numeric'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
