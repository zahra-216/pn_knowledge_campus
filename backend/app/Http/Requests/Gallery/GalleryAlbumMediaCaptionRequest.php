<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/admin/gallery-albums/{id}/media/{mediaId} — not in the API
 * Design document (its own endpoint list only has attach/detach for
 * gallery-albums media), but a caption is only meaningful once an item
 * is already in the album, so editing it after the fact needs its own
 * small endpoint — same reasoning already applied to every undocumented
 * "detach" endpoint in this project.
 */
class GalleryAlbumMediaCaptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by gallery.edit in the controller
    }

    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }
}
