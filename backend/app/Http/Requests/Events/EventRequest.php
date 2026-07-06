<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/events. `featured_image_media_id`/`gallery_media_ids`
 * reference already-uploaded Media Library items (Course/BlogPost/News's
 * pattern). No `author_id` — Events has no author field in the doc
 * (unlike News/Blog).
 */
class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by events.create/edit in the controller
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
        $eventId = $this->route('event')?->id;

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'slug' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'max:220', 'alpha_dash',
                Rule::unique('events', 'slug')->ignore($eventId),
            ],
            'venue' => ['nullable', 'string', 'max:200'],
            'is_online' => ['sometimes', 'boolean'],
            'starts_at' => [$isCreate ? 'required' : 'sometimes', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'registration_url' => ['nullable', 'string', 'max:255', 'url'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'published_at' => ['nullable', 'date'],

            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],

            'seo' => ['nullable', 'array'],
            'seo.seo_title' => ['nullable', 'string', 'max:160'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
            'seo.keywords' => ['nullable', 'string', 'max:255'],
            'seo.canonical_url' => ['nullable', 'string', 'max:255'],
            'seo.schema_type' => ['nullable', 'string', 'max:60'],
            'seo.og_title' => ['nullable', 'string', 'max:160'],
            'seo.og_description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
