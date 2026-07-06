<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/blog-categories. Slug auto-suggested from name, same
 * pattern as CourseLookupController's shared validation.
 */
class BlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by blog.create/edit in the controller
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
        $categoryId = $this->route('blogCategory')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100', Rule::unique('blog_categories', 'name')->ignore($categoryId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:120', 'alpha_dash', Rule::unique('blog_categories', 'slug')->ignore($categoryId)],
            'order' => ['sometimes', 'integer'],
        ];
    }
}
