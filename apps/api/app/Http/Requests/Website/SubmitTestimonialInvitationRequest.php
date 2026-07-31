<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTestimonialInvitationRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'quote' => ['required', 'string', 'min:10', 'max:5000'],
            'allow_publish' => ['required', 'boolean'],
            'client_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
