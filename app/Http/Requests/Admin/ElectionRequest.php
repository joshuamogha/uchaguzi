<?php

namespace App\Http\Requests\Admin;

use App\Enums\ElectionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ElectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'church_group_id' => ['nullable', 'exists:church_groups,id'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['required', Rule::in(array_keys(ElectionStatus::options()))],
            'public_results_enabled' => ['required', 'boolean'],
        ];
    }
}
