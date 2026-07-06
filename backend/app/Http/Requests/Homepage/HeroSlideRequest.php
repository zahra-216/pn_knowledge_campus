<?php

namespace App\Http\Requests\Homepage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API Design, Section 8.3 — POST/PUT /admin/hero-slides. `media_id`
 * (optional) references an already-uploaded Media Library item; the
 * controller reassigns it onto this slide's 'slide_image' collection via
 * Media::moveKeepingCustomFields() rather than accepting a raw upload
 * here (uploads always go through the Media Library endpoint first).
 */
class HeroSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by hero_slides.create/edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'cta_text' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
