<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API Design, Section 8.2 — POST/PUT /admin/courses/{id}/faqs[/{faqId}].
 */
class CourseFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by courses.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'question' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:300'],
            'answer' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
