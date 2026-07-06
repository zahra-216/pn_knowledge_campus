<?php

namespace App\Http\Requests\Applications;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/applications — public, unauthenticated. Starts a new
 * draft application (Milestone 20). Only the minimum needed to create a
 * resumable draft is required here; everything else is filled in via
 * subsequent PUT requests before the visitor submits.
 */
class ApplicationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'date_of_birth' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'international_applicant' => ['sometimes', 'boolean'],
        ];
    }
}
