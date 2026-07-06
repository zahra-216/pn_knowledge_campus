<?php

namespace App\Http\Requests\Homepage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API Design, Section 8.4 — POST/PUT /admin/partners.
 */
class PartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by partners.create/edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'url' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:partner_categories,id'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
