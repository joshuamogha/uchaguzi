<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contest_type' => ['required', Rule::in(array_keys(ContestType::options()))],
            'community_id' => ['nullable', 'exists:communities,id'],
            'required_selections' => ['required', 'integer', 'min:1'],
            'min_selections' => ['required', 'integer', 'min:1'],
            'max_selections' => ['required', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $min = (int) $this->input('min_selections');
                $max = (int) $this->input('max_selections');
                $required = (int) $this->input('required_selections');

                if ($min > $max || $required < $min || $required > $max) {
                    $validator->errors()->add('required_selections', 'Required selections must fall within the minimum and maximum selection range.');
                }
            },
        ];
    }
}
