<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/gallery-albums. No `seo` field — Gallery Albums is
 * deliberately excluded from config('seo.seoable_types') (see the
 * migration's docblock). Items attach via the separate /media
 * sub-resource, not inline here (Faculty's gallery pattern, not
 * Course's inline-at-create pattern).
 */
class GalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by gallery.create/edit in the controller
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
        $albumId = $this->route('galleryAlbum')?->id;

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'slug' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'max:170', 'alpha_dash',
                Rule::unique('gallery_albums', 'slug')->ignore($albumId),
            ],
            'description' => ['nullable', 'string'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
