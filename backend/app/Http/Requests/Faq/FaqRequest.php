<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST/PUT /admin/faqs — the global Site FAQ (Milestone 17). Distinct
 * from CourseFaqRequest (course-scoped, no category_id).
 */
class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by faq.create/edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'question' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:300'],
            'answer' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:faq_categories,id'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
