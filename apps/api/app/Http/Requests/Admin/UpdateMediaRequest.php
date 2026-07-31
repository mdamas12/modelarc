<?php

namespace App\Http\Requests\Admin;

use App\Support\MediaTaxonomy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMediaRequest extends FormRequest
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
            'category' => ['nullable', Rule::in(MediaTaxonomy::CATEGORIES)],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
            'type' => ['sometimes', Rule::in(['image', 'panorama', 'video', 'document'])],
            'original_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $category = $this->input('category');
            $subcategory = $this->input('subcategory');

            if ($category === null && $this->route('medium')) {
                $category = $this->route('medium')->category;
            }

            if (! MediaTaxonomy::isValidSubcategory($category, $subcategory)) {
                $validator->errors()->add('subcategory', 'La subcategoría no pertenece a la categoría seleccionada.');
            }
        });
    }
}
