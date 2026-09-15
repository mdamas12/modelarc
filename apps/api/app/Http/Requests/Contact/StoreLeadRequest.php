<?php

namespace App\Http\Requests\Contact;

use App\Support\BudgetRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string'],
            'budget_range' => ['required', 'string', Rule::in(BudgetRange::values())],
            'preferred_contact_method' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'project_id' => ['nullable', 'exists:projects,id'],
            // Client-generated UUID for Meta Pixel ↔ CAPI deduplication (not persisted).
            'meta_event_id' => ['nullable', 'uuid'],
            'meta_fbp' => ['nullable', 'string', 'max:255'],
            'meta_fbc' => ['nullable', 'string', 'max:255'],
            'event_source_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'budget_range.required' => 'Selecciona un rango de presupuesto.',
            'budget_range.in' => 'El rango de presupuesto no es válido.',
        ];
    }
}
