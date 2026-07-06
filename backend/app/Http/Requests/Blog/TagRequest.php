<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/tags — the standalone Tag CRUD screen (API Design,
 * Section 8.4). Separate from the create-on-the-fly tagging on
 * BlogPostRequest, which resolves tag names to ids via firstOrCreate.
 */
class TagRequest extends FormRequest
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
        $tagId = $this->route('tag')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:60', Rule::unique('tags', 'name')->ignore($tagId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:70', 'alpha_dash', Rule::unique('tags', 'slug')->ignore($tagId)],
        ];
    }
}
