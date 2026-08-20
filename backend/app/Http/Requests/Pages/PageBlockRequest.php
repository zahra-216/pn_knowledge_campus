<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API Design, Section 8.3 — POST/PUT /admin/pages/{id}/blocks[/{blockId}].
 * `block_type` is validated against config('page-blocks.types'); `data`'s
 * shape is then validated per-type here, at the application layer, per
 * the Database Design's own justification for page_blocks.data being
 * JSON rather than columns (Section 6.2).
 */
class PageBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by pages.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $blockType = $this->input('block_type') ?? $this->route('block')?->block_type;

        $rules = [
            'block_type' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::in(config('page-blocks.types'))],
            'data' => [$isCreate ? 'required' : 'sometimes', 'array'],
            'order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($blockType && ($isCreate || $this->has('data'))) {
            $rules = array_merge($rules, $this->dataRules($blockType));
        }

        return $rules;
    }

    /**
     * One case per config('page-blocks.types') entry. Adding a new block
     * type means adding both a config entry and a case here.
     */
    private function dataRules(string $blockType): array
    {
        return match ($blockType) {
            'hero' => [
                'data.heading' => ['required', 'string', 'max:200'],
                'data.subheading' => ['nullable', 'string', 'max:255'],
                'data.media_id' => ['nullable', 'integer', 'exists:media,id'],
                'data.cta_label' => ['nullable', 'string', 'max:60'],
                'data.cta_url' => ['nullable', 'string', 'max:255'],
                'data.alignment' => ['nullable', Rule::in(['left', 'center', 'right'])],
            ],
            'text', 'rich_text' => [
                'data.body' => ['required', 'string', 'max:50000'],
            ],
            'image' => [
                'data.media_id' => ['required', 'integer', 'exists:media,id'],
                'data.caption' => ['nullable', 'string', 'max:255'],
            ],
            'gallery' => [
                'data.media_ids' => ['required', 'array', 'min:1'],
                'data.media_ids.*' => ['integer', 'exists:media,id'],
            ],
            'video' => [
                'data.source' => ['required', Rule::in(['upload', 'youtube', 'vimeo'])],
                'data.media_id' => ['nullable', 'integer', 'exists:media,id', 'required_if:data.source,upload'],
                'data.url' => ['nullable', 'url', 'required_if:data.source,youtube,vimeo'],
                'data.caption' => ['nullable', 'string', 'max:255'],
            ],
            'cta' => [
                'data.heading' => ['required', 'string', 'max:200'],
                'data.body' => ['nullable', 'string'],
                'data.button_label' => ['required', 'string', 'max:60'],
                'data.button_url' => ['required', 'string', 'max:255'],
                'data.style' => ['nullable', Rule::in(['primary', 'secondary'])],
            ],
            'faq' => [
                'data.items' => ['required', 'array', 'min:1'],
                'data.items.*.question' => ['required', 'string', 'max:255'],
                'data.items.*.answer' => ['required', 'string'],
            ],
            'statistics' => [
                'data.items' => ['required', 'array', 'min:1'],
                'data.items.*.label' => ['required', 'string', 'max:100'],
                'data.items.*.value' => ['required', 'string', 'max:30'],
                'data.items.*.icon' => ['nullable', 'string', 'max:60'],
            ],
            'testimonials' => [
                'data.items' => ['required', 'array', 'min:1'],
                'data.items.*.quote' => ['required', 'string'],
                'data.items.*.name' => ['required', 'string', 'max:100'],
                'data.items.*.role' => ['nullable', 'string', 'max:100'],
                'data.items.*.avatar_media_id' => ['nullable', 'integer', 'exists:media,id'],
            ],
            'partners' => [
                'data.items' => ['required', 'array', 'min:1'],
                'data.items.*.name' => ['required', 'string', 'max:100'],
                'data.items.*.logo_media_id' => ['required', 'integer', 'exists:media,id'],
                'data.items.*.url' => ['nullable', 'string', 'max:255'],
            ],
            'chairman_message' => [
                'data.heading' => ['nullable', 'string', 'max:200'],
                'data.name' => ['required', 'string', 'max:100'],
                'data.role' => ['nullable', 'string', 'max:100'],
                'data.qualifications' => ['nullable', 'string', 'max:255'],
                'data.message' => ['required', 'string'],
                'data.media_id' => ['nullable', 'integer', 'exists:media,id'],
            ],
            'management_board' => [
                'data.items' => ['required', 'array', 'min:1'],
                'data.items.*.name' => ['required', 'string', 'max:100'],
                'data.items.*.position' => ['required', 'string', 'max:100'],
                'data.items.*.qualifications' => ['nullable', 'string', 'max:255'],
                'data.items.*.photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            ],
            default => [],
        };
    }
}
