<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

/**
 * POST/PUT /api/v1/admin/roles — SRS FR-29: "allowing Super Admins to
 * create custom roles" beyond the five baseline ones. `name` is only
 * validated as required/unique here; RoleController separately blocks
 * renaming one of the five baseline roles (see its BASELINE_ROLES
 * constant) regardless of what passes validation.
 */
class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by roles.* in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'name' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'max:100',
                Rule::unique('roles', 'name')->where('guard_name', 'sanctum')->ignore($this->route('role')),
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::exists(Permission::class, 'name')->where('guard_name', 'sanctum')],
        ];
    }
}
