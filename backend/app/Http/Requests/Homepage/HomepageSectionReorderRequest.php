<?php

namespace App\Http\Requests\Homepage;

use App\Models\HomepageSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PATCH /api/v1/admin/homepage-sections/reorder (API Design, Section
 * 8.3) — "Reorder / toggle sections." Body: { "sections": [{
 * "section_key": "hero", "order": 0, "is_enabled": true }, ...] },
 * covering every section — mirrors the Menu/Page block reorder shape.
 */
class HomepageSectionReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by homepage.edit in the controller
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.section_key' => ['required', 'string', Rule::in(HomepageSection::SECTIONS)],
            'sections.*.order' => ['required', 'integer'],
            'sections.*.is_enabled' => ['required', 'boolean'],
        ];
    }
}
