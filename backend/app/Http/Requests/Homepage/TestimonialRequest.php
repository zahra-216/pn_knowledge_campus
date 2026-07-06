<?php

namespace App\Http\Requests\Homepage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API Design, Section 8.4 — POST/PUT /admin/testimonials. `course_id`
 * now has a real `exists:courses,id` check — Course Management shipped
 * the table and the FK constraint alongside it.
 */
class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by testimonials.create/edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'role_title' => ['nullable', 'string', 'max:150'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'content' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_featured' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
