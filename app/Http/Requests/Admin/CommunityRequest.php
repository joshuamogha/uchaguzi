<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $communityId = $this->route('community')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('communities', 'name')->ignore($communityId)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
