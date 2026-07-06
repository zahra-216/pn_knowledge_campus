<?php

namespace App\Http\Requests\Faculties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * API Design, Section 8.2 — POST/PUT /admin/faculties. Slugs are
 * auto-suggested from the name (Database Design, Section 2.3), same
 * pattern as PageRequest. `icon_media_id`/`banner_media_id`/
 * `dean_photo_media_id` reference already-uploaded Media Library items;
 * the controller reassigns them via Media::moveKeepingCustomFields()
 * rather than accepting raw uploads here. Gallery management (multiple
 * images) is a separate sub-resource — FacultyGalleryRequest — mirroring
 * the API Design's gallery-albums attach/detach pattern (Section 8.4),
 * since a single-file replace doesn't fit a multi-item collection.
 */
class FacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by faculties.create/edit in the controller
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $facultyId = $this->route('faculty')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'slug' => [
                $isCreate ? 'required' : 'sometimes',
                'string',
                'max:170',
                'alpha_dash',
                Rule::unique('faculties', 'slug')->ignore($facultyId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'dean_name' => ['nullable', 'string', 'max:150'],
            'dean_title' => ['nullable', 'string', 'max:150'],
            'dean_message' => ['nullable', 'string'],
            'order' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'icon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'banner_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'dean_photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
