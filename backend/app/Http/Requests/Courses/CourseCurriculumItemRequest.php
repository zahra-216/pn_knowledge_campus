<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API Design, Section 8.2 — POST/PUT /admin/courses/{id}/curriculum[/{itemId}].
 */
class CourseCurriculumItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by courses.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:course_curriculum_items,id'],
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'duration' => ['nullable', 'string', 'max:60'],
            'order' => ['sometimes', 'integer'],
        ];
    }
}
