<?php

namespace App\Http\Requests\Downloads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/download-categories. Slug auto-suggested from name,
 * same pattern as BlogCategoryRequest/PartnerCategoryRequest/FaqCategoryRequest.
 */
class DownloadCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by downloads.create/edit in the controller
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
        $categoryId = $this->route('downloadCategory')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100', Rule::unique('download_categories', 'name')->ignore($categoryId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:120', 'alpha_dash', Rule::unique('download_categories', 'slug')->ignore($categoryId)],
            'order' => ['sometimes', 'integer'],
        ];
    }
}
