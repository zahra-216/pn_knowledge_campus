<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PATCH /api/v1/admin/inquiries/{inquiry}/status — the admin inbox's
 * only mutation on an existing inquiry (status is the only column with
 * a write path here; the captured content itself is never editable).
 */
class UpdateInquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by inquiries.manage in the controller
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:new,in_progress,resolved,spam'],
        ];
    }
}
