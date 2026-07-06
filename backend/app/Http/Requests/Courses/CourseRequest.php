<?php

namespace App\Http\Requests\Courses;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * API Design, Section 9.4 — POST/PUT /admin/courses. Matches the
 * document's full nested example payload: featured_image_media_id/
 * gallery_media_ids/downloads_media_ids reference already-uploaded Media
 * Library items; curriculum accepts a nested module→lesson array inline
 * at create time (in addition to the separate /curriculum sub-resource
 * for later edits); seo accepts inline SEO fields (in addition to the
 * standalone /admin/seo/course/{id} endpoint).
 */
class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by courses.create/edit in the controller
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('course_name')) {
            $this->merge(['slug' => Str::slug($this->input('course_name'))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $courseId = $this->route('course')?->id;

        return [
            'faculty_id' => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:faculties,id'],
            'department_id' => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:departments,id'],
            'level_id' => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:course_levels,id'],
            'mode_id' => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:course_modes,id'],
            'category_id' => ['nullable', 'integer', 'exists:course_categories,id'],
            'course_name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'course_code' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'max:30',
                Rule::unique('courses', 'course_code')->ignore($courseId),
            ],
            'slug' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'max:220', 'alpha_dash',
                Rule::unique('courses', 'slug')->ignore($courseId),
            ],
            'duration_value' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1'],
            'duration_unit' => [$isCreate ? 'required' : 'sometimes', Rule::in(['day', 'week', 'month', 'year'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'price_currency' => ['nullable', 'string', 'size:3'],
            'overview' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:500'],
            'description' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'entry_requirements' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'string'],
            'career_opportunities' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer'],

            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],
            'downloads_media_ids' => ['nullable', 'array'],
            'downloads_media_ids.*' => ['integer', 'exists:media,id'],

            'curriculum' => ['nullable', 'array'],
            'curriculum.*.title' => ['required', 'string', 'max:200'],
            'curriculum.*.description' => ['nullable', 'string'],
            'curriculum.*.duration' => ['nullable', 'string', 'max:60'],
            'curriculum.*.order' => ['sometimes', 'integer'],
            'curriculum.*.children' => ['nullable', 'array'],
            'curriculum.*.children.*.title' => ['required', 'string', 'max:200'],
            'curriculum.*.children.*.description' => ['nullable', 'string'],
            'curriculum.*.children.*.duration' => ['nullable', 'string', 'max:60'],
            'curriculum.*.children.*.order' => ['sometimes', 'integer'],

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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // "A Course's department_id must belong to the same
            // faculty_id submitted in the same request" (API Design,
            // Section 6.2) — checked via this custom rule, not just two
            // independent exists checks.
            $facultyId = $this->input('faculty_id');
            $departmentId = $this->input('department_id');

            if (! $departmentId || (! $facultyId && $this->isMethod('put'))) {
                return;
            }

            $department = Department::find($departmentId);

            if (! $department) {
                return;
            }

            $effectiveFacultyId = $facultyId ?? $this->route('course')?->faculty_id;

            if ($effectiveFacultyId && $department->faculty_id !== (int) $effectiveFacultyId) {
                $validator->errors()->add('department_id', 'The selected department does not belong to the selected faculty.');
            }
        });
    }
}
