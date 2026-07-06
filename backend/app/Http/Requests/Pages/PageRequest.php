<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * API Design, Section 8.3 — POST/PUT /admin/pages. Slugs are
 * "auto-suggested from the title but editable" (Database Design, Section
 * 2.3) — prepareForValidation() fills one in from the title whenever the
 * request omits it, so a CMS user never has to hand-craft one.
 */
class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by pages.create/pages.edit in the controller
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('title')) {
            $this->merge(['slug' => Str::slug($this->input('title'))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $pageId = $this->route('page')?->id;

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'slug' => [
                $isCreate ? 'required' : 'sometimes',
                'string',
                'max:220',
                'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($pageId),
            ],
            'template' => ['nullable', 'string', 'max:60'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
