<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroRequest extends FormRequest
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
            'text_1' => ['nullable', 'string', 'max:255'],
            'text_2' => ['nullable', 'string'],
            'text_3' => ['nullable', 'string'],
        ];
    }
}
