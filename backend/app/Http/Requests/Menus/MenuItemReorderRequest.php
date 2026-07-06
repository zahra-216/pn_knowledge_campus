<?php

namespace App\Http\Requests\Menus;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PATCH /api/v1/admin/menus/{menu}/items/reorder (API Design, Section
 * 8.3) — "Bulk-update item order/nesting." Body shape:
 * { "items": [{ "id": 5, "parent_id": null, "order": 0 }, ...] } — a
 * flat list covering every item in the menu; the admin UI's drag-and-drop
 * tree sends its complete current structure on every reorder, which
 * MenuItemController::reorder() applies in a single transaction.
 */
class MenuItemReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by menus.edit in the controller
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'items.*.order' => ['required', 'integer'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $entry) {
                if (($entry['parent_id'] ?? null) === ($entry['id'] ?? null)) {
                    $validator->errors()->add("items.{$index}.parent_id", 'An item cannot be its own parent.');
                }
            }
        });
    }
}
