<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/inquiries — public, unauthenticated. Backs the Contact
 * page's form and Course Detail's "Enquire Now" modal (UI/UX Design,
 * Flow A step 5 / Flow C step 5's "International Applicant flag").
 */
class InquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint, no permission to gate
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
            'source_page' => ['nullable', 'string', 'max:255'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'international_applicant' => ['sometimes', 'boolean'],
        ];
    }
}
