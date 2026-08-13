<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PATCH /api/v1/admin/inquiries/{inquiry}/assign — audit fix (High
 * remediation). `assigned_to: null` unassigns.
 */
class AssignInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by inquiries.manage in the controller
    }

    public function rules(): array
    {
        return [
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
