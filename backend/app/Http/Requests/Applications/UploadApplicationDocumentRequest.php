<?php

namespace App\Http\Requests\Applications;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/applications/{application}/documents — public,
 * unauthenticated (ownership verified via `email`, see
 * ApplicationController::authorizeOwner()). Deliberately narrower
 * mime/size allowance than the admin Media Library's ValidatesMediaFile
 * trait — this is an unauthenticated upload endpoint, so it stays
 * restrictive (transcripts/IDs/photos only, 5MB cap) rather than reusing
 * the admin trait's broader allowance.
 */
class UploadApplicationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'label' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
