<?php

namespace App\Http\Requests\Faculties;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST /api/v1/admin/faculties/{faculty}/gallery — attach one or more
 * already-uploaded Media Library items to this faculty's gallery,
 * mirroring the gallery-albums attach endpoint (API Design, Section 8.4).
 */
class FacultyGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by faculties.edit in the controller
    }

    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ];
    }
}
