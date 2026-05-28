<?php

namespace App\Http\Requests\Voting;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'pin' => ['nullable', 'digits:6'],
        ];
    }
}
