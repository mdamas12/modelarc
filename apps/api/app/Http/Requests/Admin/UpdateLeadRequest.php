<?php

namespace App\Http\Requests\Admin;

use App\Support\BudgetRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
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
            // Phase 1 statuses. Future CRM pipeline may expand to:
            // new, qualification, quote_sent, negotiation, won, closed
            'status' => ['sometimes', Rule::in(['new', 'in_progress', 'closed'])],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string'],
            'budget_range' => ['nullable', 'string', Rule::in(BudgetRange::values())],
            'preferred_contact_method' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }
}
