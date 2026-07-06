<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PATCH /api/v1/admin/pages/{id}/blocks/reorder (API Design, Section
 * 8.3) — "Bulk-update block order." Body: { "items": [{ "id": 5, "order":
 * 0 }, ...] }, covering every block on the page. Blocks are a flat
 * ordered list (no nesting, unlike menu items), so there's no parent_id
 * to validate here.
 */
class PageBlockReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by pages.edit in the controller
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:page_blocks,id'],
            'items.*.order' => ['required', 'integer'],
        ];
    }
}
