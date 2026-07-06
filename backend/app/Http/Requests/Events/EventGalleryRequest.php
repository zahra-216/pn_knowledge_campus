<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/events/{id}/media — attach one or more
 * already-uploaded Media Library items to this event's gallery,
 * mirroring BlogPostGalleryRequest/NewsGalleryRequest's same pattern.
 */
class EventGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by events.edit in the controller
    }

    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ];
    }
}
