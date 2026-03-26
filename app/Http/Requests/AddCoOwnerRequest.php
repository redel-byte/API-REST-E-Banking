<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCoOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be a number',
            'user_id.exists' => 'User not found',
        ];
    }
}
