<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PATCH /api/v1/admin/course-categories/reorder — bulk order/nesting
 * update, applied in one transaction. Same shape as
 * MenuItemController::reorder / CourseCurriculumReorderRequest.
 */
class CourseCategoryReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by courses.edit in the controller
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:course_categories,id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:course_categories,id'],
            'items.*.order' => ['required', 'integer'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $entry) {
                if (($entry['parent_id'] ?? null) === ($entry['id'] ?? null)) {
                    $validator->errors()->add("items.{$index}.parent_id", 'A category cannot be its own parent.');
                }
            }
        });
    }
}
