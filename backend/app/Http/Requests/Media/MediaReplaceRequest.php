<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Media\Concerns\ValidatesMediaFile;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/media/{id}/replace (Media Library hardening —
 * "Replace files"). Same type/size rules as upload; alt_text is required
 * whenever the replacement file is an image, exactly as on create — the
 * service layer's fallback-to-existing-alt-text only applies when this
 * rule doesn't fire (i.e. replacing a non-image).
 */
class MediaReplaceRequest extends FormRequest
{
    use ValidatesMediaFile;

    public function authorize(): bool
    {
        return true; // gated by media.edit in the controller
    }

    public function rules(): array
    {
        return [
            'file' => $this->fileRules(),
            'alt_text' => ['nullable', 'string', 'max:255'],
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
