<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Media\Concerns\ValidatesMediaFile;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/media (API Design, Section 9.6 — multipart/form-data).
 * SRS Section 7.3: Images, Videos, PDF, Documents are the supported
 * types; alt text is mandatory on images specifically (accessibility/SEO),
 * enforced below since it can't be expressed as a static rule until the
 * file's mime type is known.
 */
class MediaUploadRequest extends FormRequest
{
    use ValidatesMediaFile;

    public function authorize(): bool
    {
        return true; // gated by media.create in the controller
    }

    public function rules(): array
    {
        return [
            'file' => $this->fileRules(),
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'collection_name' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->fileIsImage() && ! $this->filled('alt_text')) {
                $validator->errors()->add('alt_text', 'Alt text is required for image uploads.');
            }
        });
    }
}
