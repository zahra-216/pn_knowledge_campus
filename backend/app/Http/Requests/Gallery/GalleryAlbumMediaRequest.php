<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/gallery-albums/{id}/media — "Attach Media Library
 * items to an album" (API Design, Section 8.4). Unlike every other
 * gallery attach endpoint in this project (Faculty/Course/BlogPost/News/
 * Event), each item here carries its own optional caption (Database
 * Design, Section 4.6: "each carrying its own caption in Spatie's
 * per-file custom_properties"), so the payload is an array of objects
 * rather than a flat media_ids array.
 */
class GalleryAlbumMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by gallery.edit in the controller
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.media_id' => ['required', 'integer', 'exists:media,id'],
            'items.*.caption' => ['nullable', 'string', 'max:255'],
        ];
    }
}
