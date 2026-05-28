<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChurchGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $groupId = $this->route('church_group')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('church_groups', 'name')->ignore($groupId)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
