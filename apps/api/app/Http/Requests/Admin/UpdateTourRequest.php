<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTourRequest extends FormRequest
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
            'project_id' => ['sometimes', 'exists:projects,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'autorotate_enabled' => ['sometimes', 'boolean'],
            'autorotate_speed' => ['nullable', 'numeric'],
            'show_compass' => ['sometimes', 'boolean'],
            'show_scene_selector' => ['sometimes', 'boolean'],
            'initial_scene_id' => ['nullable', 'exists:tour_scenes,id'],
        ];
    }
}
