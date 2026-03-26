<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'to_account_id' => ['required', 'integer', 'exists:accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_account_id.required' => 'Source account ID is required',
            'from_account_id.integer' => 'Source account ID must be a number',
            'from_account_id.exists' => 'Source account not found',
            'to_account_id.required' => 'Destination account ID is required',
            'to_account_id.integer' => 'Destination account ID must be a number',
            'to_account_id.exists' => 'Destination account not found',
            'to_account_id.different' => 'Source and destination accounts must be different',
            'amount.required' => 'Transfer amount is required',
            'amount.numeric' => 'Transfer amount must be a number',
            'amount.min' => 'Transfer amount must be at least 0.01 MAD',
            'amount.max' => 'Transfer amount cannot exceed 10,000 MAD',
            'description.string' => 'Description must be text',
            'description.max' => 'Description cannot exceed 255 characters',
        ];
    }
}
