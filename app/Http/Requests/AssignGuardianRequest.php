<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guardian_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'guardian_id.required' => 'Guardian ID is required',
            'guardian_id.integer' => 'Guardian ID must be a number',
            'guardian_id.exists' => 'Guardian user not found',
        ];
    }
}
