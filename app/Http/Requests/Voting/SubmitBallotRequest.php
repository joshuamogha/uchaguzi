<?php

namespace App\Http\Requests\Voting;

use Illuminate\Foundation\Http\FormRequest;

class SubmitBallotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selections' => ['required', 'array'],
        ];
    }
}
