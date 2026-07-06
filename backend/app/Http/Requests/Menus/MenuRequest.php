<?php

namespace App\Http\Requests\Menus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by menus.edit in the controller
    }

    public function rules(): array
    {
        $menuId = $this->route('menu')?->id;

        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('menus', 'name')->ignore($menuId)],
        ];
    }
}
