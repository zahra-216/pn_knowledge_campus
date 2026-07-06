<?php

namespace App\Http\Requests\News;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/news/{id}/media — attach one or more
 * already-uploaded Media Library items to this article's gallery,
 * mirroring BlogPostGalleryRequest's same pattern.
 */
class NewsGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by news.edit in the controller
    }

    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ];
    }
}
