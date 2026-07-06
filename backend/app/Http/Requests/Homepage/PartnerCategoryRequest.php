<?php

namespace App\Http\Requests\Homepage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/partner-categories. Slug auto-suggested from name,
 * same pattern as BlogCategoryRequest.
 */
class PartnerCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by partners.create/edit in the controller
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $categoryId = $this->route('partnerCategory')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100', Rule::unique('partner_categories', 'name')->ignore($categoryId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:120', 'alpha_dash', Rule::unique('partner_categories', 'slug')->ignore($categoryId)],
            'order' => ['sometimes', 'integer'],
        ];
    }
}
