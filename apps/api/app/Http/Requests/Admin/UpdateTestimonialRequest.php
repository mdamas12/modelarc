<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
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
            'client_name' => ['sometimes', 'string', 'max:255'],
            'client_photo_media_id' => ['nullable', 'exists:media,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_label' => ['nullable', 'string', 'max:255'],
            'quote' => ['sometimes', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
