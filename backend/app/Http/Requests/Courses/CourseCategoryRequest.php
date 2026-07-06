<?php

namespace App\Http\Requests\Courses;

use App\Models\CourseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * POST/PUT /admin/course-categories. Slug auto-suggested from name, same
 * pattern as FacultyRequest. `icon_media_id`/`image_media_id` reference
 * already-uploaded Media Library items; the controller reassigns them
 * via Media::moveKeepingCustomFields() rather than accepting raw
 * uploads here (same pattern as Faculty's icon/banner).
 */
class CourseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by courses.create/edit in the controller
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
        $categoryId = $this->route('courseCategory')?->id;

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:60', Rule::unique('course_categories', 'name')->ignore($categoryId)],
            'slug' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:70', 'alpha_dash', Rule::unique('course_categories', 'slug')->ignore($categoryId)],
            'order' => ['sometimes', 'integer'],
            'parent_id' => ['nullable', 'integer', Rule::exists('course_categories', 'id')],
            'icon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $categoryId = $this->route('courseCategory')?->id;

            if (! $parentId || ! $categoryId) {
                return;
            }

            if ($parentId === $categoryId) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');

                return;
            }

            if ($this->wouldCreateCycle($parentId, $categoryId)) {
                $validator->errors()->add('parent_id', 'A category cannot be moved under one of its own subcategories.');
            }
        });
    }

    /**
     * Walks up from the candidate parent toward the root, checking
     * whether $categoryId appears in that ancestor chain — if it does,
     * the candidate parent is currently a descendant of $categoryId, and
     * assigning it as the parent would close a cycle.
     */
    private function wouldCreateCycle(int $candidateParentId, int $categoryId): bool
    {
        $current = CourseCategory::find($candidateParentId);

        while ($current !== null) {
            if ($current->id === $categoryId) {
                return true;
            }

            $current = $current->parent_id ? CourseCategory::find($current->parent_id) : null;
        }

        return false;
    }
}
