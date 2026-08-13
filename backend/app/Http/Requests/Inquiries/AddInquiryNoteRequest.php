<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/inquiries/{inquiry}/notes — audit fix (High
 * remediation).
 */
class AddInquiryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by inquiries.manage in the controller
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
