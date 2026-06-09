<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$user?->exists ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var User|null $user */
                $user = $this->route('user');
                $actor = $this->user();

                if (! $user || ! $user->exists || ! $actor) {
                    return;
                }

                if ($actor->is($user) && ! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'You cannot deactivate your own account.');
                }

                if ($actor->is($user) && ! $this->boolean('is_admin')) {
                    $validator->errors()->add('is_admin', 'You cannot remove your own admin access.');
                }

                $activeAdminCount = User::query()
                    ->where('is_admin', true)
                    ->where('is_active', true)
                    ->when($user->exists, fn ($query) => $query->whereKeyNot($user->id))
                    ->count();

                if ($user->is_admin && $user->is_active && (! $this->boolean('is_admin') || ! $this->boolean('is_active')) && $activeAdminCount === 0) {
                    $validator->errors()->add('is_admin', 'The system must keep at least one active admin user.');
                }
            },
        ];
    }
}
