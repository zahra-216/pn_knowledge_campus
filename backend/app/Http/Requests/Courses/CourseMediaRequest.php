<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /api/v1/admin/courses/{id}/media (API Design, Section 8.2) —
 * "Attach an existing Media Library item to this course's
 * gallery/downloads collection."
 */
class CourseMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by courses.edit in the controller
    }

    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
            'collection' => ['required', Rule::in(['gallery', 'downloads'])],
        ];
    }
}
