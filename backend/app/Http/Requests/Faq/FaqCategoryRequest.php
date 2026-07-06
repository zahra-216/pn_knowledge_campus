<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/faq-categories. Slug auto-suggested from name, same
 * pattern as BlogCategoryRequest/PartnerCategoryRequest.
 */
class FaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by faq.create/edit in the controller
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
        $categoryId = $this->route('faqCategory')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100', Rule::unique('faq_categories', 'name')->ignore($categoryId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:120', 'alpha_dash', Rule::unique('faq_categories', 'slug')->ignore($categoryId)],
            'order' => ['sometimes', 'integer'],
        ];
    }
}
