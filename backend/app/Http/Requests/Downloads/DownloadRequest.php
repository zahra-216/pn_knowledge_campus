<?php

namespace App\Http\Requests\Downloads;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST/PUT /admin/downloads.
 */
class DownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by downloads.create/edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:download_categories,id'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'requires_inquiry' => ['sometimes', 'boolean'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
