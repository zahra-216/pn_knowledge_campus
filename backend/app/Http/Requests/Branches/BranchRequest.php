<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by BranchController's store and update — API Design's validation
 * conventions (Section 5.2) don't differ between create/edit for this
 * resource, so one Form Request covers both.
 */
class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by settings.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'address' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'city' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_head_office' => ['boolean'],
            'order' => ['integer'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'is_head_office' => $this->has('is_head_office') ? $this->boolean('is_head_office') : null,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : null,
        ], fn ($v) => $v !== null));
    }
}
