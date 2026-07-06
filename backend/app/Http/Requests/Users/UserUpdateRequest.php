<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PUT /api/v1/admin/users/{user} — password is optional here (omit to
 * leave it unchanged), unlike UserCreateRequest where it's required.
 */
class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by users.edit in the controller
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'string', 'email', 'max:191', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'nullable', 'string', 'min:10', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['sometimes', 'string', Rule::exists('roles', 'name')->where('guard_name', 'sanctum')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
