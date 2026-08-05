<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryChangeRequest extends FormRequest
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
            'before_media_id' => ['required', 'integer', 'exists:media,id'],
            'design_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'after_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'compare_with' => ['nullable', Rule::in(['design', 'after'])],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
