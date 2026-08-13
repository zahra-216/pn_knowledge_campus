<?php

namespace App\Http\Requests\Downloads;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /api/v1/admin/downloads/{download}/attach — audit fix (High
 * remediation). `attachable_type` is validated against
 * config('downloads.attachable_types'), the same short-key allow-list
 * pattern as config('seo.seoable_types')/config('menus.linkable_types').
 */
class AttachDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by downloads.edit in the controller
    }

    public function rules(): array
    {
        return [
            'attachable_type' => ['required', 'string', Rule::in(array_keys(config('downloads.attachable_types')))],
            'attachable_id' => ['required', 'integer'],
        ];
    }
}
