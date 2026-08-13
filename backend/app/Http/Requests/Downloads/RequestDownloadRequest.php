<?php

namespace App\Http\Requests\Downloads;

use App\Models\Download;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/downloads/{download}/request — audit fix (High
 * remediation), FR-06's gated-download capture form. name/email are
 * only required when the target Download actually has
 * `requires_inquiry` set — an ungated download can still be "requested"
 * through this same endpoint (for a consistent download_count and a
 * consistent frontend flow) without forcing a visitor to fill in a form
 * for a file that was never meant to be gated.
 */
class RequestDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint, no permission to gate
    }

    public function rules(): array
    {
        /** @var Download $download */
        $download = $this->route('download');
        $required = $download->requires_inquiry ? 'required' : 'nullable';

        return [
            'name' => [$required, 'string', 'max:150'],
            'email' => [$required, 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
