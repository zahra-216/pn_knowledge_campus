<?php

namespace App\Http\Requests\News;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/news-categories. Slug auto-suggested from name, same
 * pattern as BlogCategoryRequest.
 */
class NewsCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by news.create/edit in the controller
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
        $categoryId = $this->route('newsCategory')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100', Rule::unique('news_categories', 'name')->ignore($categoryId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:120', 'alpha_dash', Rule::unique('news_categories', 'slug')->ignore($categoryId)],
            'order' => ['sometimes', 'integer'],
        ];
    }
}
