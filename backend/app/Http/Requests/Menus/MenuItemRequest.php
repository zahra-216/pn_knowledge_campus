<?php

namespace App\Http\Requests\Menus;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API Design, Section 5.3: "A menu_items row must supply exactly one of
 * (linkable_type + linkable_id) or custom_url, never both, never
 * neither." linkable_type is additionally validated against
 * config('menus.linkable_types') — page/faculty/department/course/
 * blog_post/news/event/gallery_album are real entries as of the Public
 * Website milestone; anything else (including a made-up type) is
 * rejected the same way it always has been.
 */
class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by menus.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'label' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:100'],
            'linkable_type' => ['nullable', 'string'],
            'linkable_id' => ['nullable', 'integer', 'required_with:linkable_type'],
            'custom_url' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_mega_menu' => ['boolean'],
            'open_in_new_tab' => ['boolean'],
            'order' => ['integer'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'visible_on' => ['in:both,desktop,mobile'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasLinkable = $this->filled('linkable_type');
            $hasCustomUrl = $this->filled('custom_url');

            // On update, only enforce this when either field is actually
            // being touched — a PUT that only changes e.g. `order` isn't
            // required to resend the link target.
            if ($this->isMethod('post') || $this->has('linkable_type') || $this->has('custom_url')) {
                if ($hasLinkable && $hasCustomUrl) {
                    $validator->errors()->add('custom_url', 'Provide either an internal link or a custom URL, not both.');
                }

                if (! $hasLinkable && ! $hasCustomUrl && $this->isMethod('post')) {
                    $validator->errors()->add('custom_url', 'Provide either an internal link or a custom URL.');
                }
            }

            if ($hasLinkable && ! config("menus.linkable_types.{$this->input('linkable_type')}")) {
                $validator->errors()->add('linkable_type', "\"{$this->input('linkable_type')}\" is not a recognized internal link type.");
            }
        });
    }
}
