<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/admin/media/{id} — update alt_text and/or move to a
 * different folder (API Design, Section 8.6). Nothing else about an
 * uploaded file is editable after the fact.
 */
class MediaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by media.edit in the controller
    }

    public function rules(): array
    {
        return [
            'alt_text' => ['sometimes', 'nullable', 'string', 'max:255'],
            'folder_id' => ['sometimes', 'nullable', 'integer', 'exists:media_folders,id'],
        ];
    }
}
