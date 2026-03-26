<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'string', Rule::in(['COURANT', 'EPARGNE', 'MINEUR'])],
            'initial_deposit' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'overdraft_limit' => ['nullable', 'numeric', 'min:0', 'max:50000'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ];

        if ($this->input('type') === 'MINEUR') {
            $rules['guardian_id'] = ['required', 'integer', 'exists:users,id'];
        }

        if ($this->input('type') === 'COURANT') {
            $rules['overdraft_limit'] = ['nullable', 'numeric', 'min:0', 'max:50000'];
        }

        if (in_array($this->input('type'), ['EPARGNE', 'MINEUR'])) {
            $rules['interest_rate'] = ['required', 'numeric', 'min:0', 'max:20'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Account type is required',
            'type.in' => 'Account type must be one of: COURANT, EPARGNE, MINEUR',
            'initial_deposit.numeric' => 'Initial deposit must be a number',
            'initial_deposit.min' => 'Initial deposit cannot be negative',
            'initial_deposit.max' => 'Initial deposit cannot exceed 1,000,000 MAD',
            'overdraft_limit.numeric' => 'Overdraft limit must be a number',
            'overdraft_limit.min' => 'Overdraft limit cannot be negative',
            'overdraft_limit.max' => 'Overdraft limit cannot exceed 50,000 MAD',
            'interest_rate.required' => 'Interest rate is required for savings and minor accounts',
            'interest_rate.numeric' => 'Interest rate must be a number',
            'interest_rate.min' => 'Interest rate cannot be negative',
            'interest_rate.max' => 'Interest rate cannot exceed 20%',
            'guardian_id.required' => 'Guardian is required for minor accounts',
            'guardian_id.exists' => 'Guardian user not found',
        ];
    }
}
