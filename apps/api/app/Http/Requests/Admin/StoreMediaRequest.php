<?php

namespace App\Http\Requests\Admin;

use App\Support\MediaTaxonomy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMediaRequest extends FormRequest
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
            // 100 MB — panoramas 360° de alta calidad suelen pesar 30–80 MB
            'file' => ['required', 'file', 'max:102400'],
            'type' => ['nullable', Rule::in(['image', 'panorama', 'video', 'document'])],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! MediaTaxonomy::isValidCategory($this->input('category'))) {
                $validator->errors()->add('category', 'La categoría no es válida.');
            }

            if (! MediaTaxonomy::isValidSubcategory($this->input('category'), $this->input('subcategory'))) {
                $validator->errors()->add('subcategory', 'La subcategoría no pertenece a la categoría seleccionada.');
            }
        });
    }
}
