<?php

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * API Design, Section 8.2 — POST/PUT /admin/departments. Slugs are
 * auto-suggested from the name, same pattern as FacultyRequest.
 * `banner_media_id` references an already-uploaded Media Library item;
 * the controller reassigns it via Media::moveKeepingCustomFields().
 */
class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by departments.create/edit in the controller
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
        $departmentId = $this->route('department')?->id;

        return [
            'faculty_id' => [$isCreate ? 'required' : 'sometimes', 'integer', 'exists:faculties,id'],
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'slug' => [
                $isCreate ? 'required' : 'sometimes',
                'string',
                'max:170',
                'alpha_dash',
                Rule::unique('departments', 'slug')->ignore($departmentId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'order' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'banner_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
