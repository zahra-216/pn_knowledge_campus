<?php

namespace App\Http\Requests\Applications;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/applications/{application} — public, unauthenticated.
 * `email` doubles as the ownership credential (see
 * ApplicationController::authorizeOwner()) and, if changed, the new
 * email of record — same field serves both purposes, same as
 * ApplicationCreateRequest.
 */
class ApplicationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'new_email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'course_id' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string'],
            'international_applicant' => ['sometimes', 'boolean'],
        ];
    }
}
