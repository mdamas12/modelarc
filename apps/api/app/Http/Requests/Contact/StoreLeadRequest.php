<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string'],
            'budget_range' => ['nullable', 'string', 'max:100'],
            'preferred_contact_method' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ];
    }
}
