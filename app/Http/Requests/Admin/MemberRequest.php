<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'member_no' => ['nullable', 'string', 'max:100', Rule::unique('members', 'member_no')->ignore($memberId)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'community_id' => ['nullable', 'exists:communities,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
