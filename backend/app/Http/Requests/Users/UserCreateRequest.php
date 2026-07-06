<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by users.create in the controller
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'sanctum')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
