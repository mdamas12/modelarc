<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'project_type_id' => ['nullable', 'exists:project_types,id'],
            // Legacy string kept optional/derived: prefer category_id, resolved into `category` on save.
            'category' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'subcategory_id' => [
                'nullable',
                Rule::exists('subcategories', 'id')->where(function ($query) {
                    if ($this->filled('category_id')) {
                        $query->where('category_id', $this->input('category_id'));
                    }
                }),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'status' => ['nullable', Rule::in(['en_ejecucion', 'finalizado'])],
            'area' => ['nullable', 'string', 'max:100'],
            'duration' => ['nullable', 'string', 'max:100'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'cover_media_id' => ['nullable', 'exists:media,id'],
            'is_featured' => ['sometimes', 'boolean'],
            'has_virtual_tour' => ['sometimes', 'boolean'],
            'publication_status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'media' => ['nullable', 'array'],
            'media.*.media_id' => ['required_with:media', 'exists:media,id'],
            'media.*.type' => ['nullable', Rule::in(['gallery', 'render', 'plan', 'before', 'after', 'video'])],
            'media.*.title' => ['nullable', 'string', 'max:255'],
            'media.*.description' => ['nullable', 'string'],
            'media.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'media.*.is_cover' => ['sometimes', 'boolean'],
            'media.*.subcategory' => ['nullable', 'string', 'max:100'],
            'media.*.is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('category_id') && ! $this->filled('category')) {
                $validator->errors()->add('category_id', 'Selecciona una categoría.');
            }
        });
    }
}
