<?php

namespace App\Http\Requests\Homepage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/admin/homepage-content — bulk update by key, restricted to
 * config('settings.homepage')'s keys specifically (not the full Settings
 * registry). These rows live in the same `settings` table as everything
 * else (Database Design, Section 4.2 — no new table needed for flat
 * marketing copy), but are gated by homepage.edit rather than
 * settings.edit: the SRS Permission Matrix gives Marketing edit rights
 * over Homepage Builder content, while Settings proper stays Super
 * Admin-only. Reusing SettingController's endpoint would have
 * incorrectly put this behind the Settings module's stricter gate.
 */
class UpdateHomepageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by homepage.edit in the controller
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'array', 'min:1'],
            'content.*' => ['nullable', 'string', 'max:65535'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $allowedKeys = array_keys(config('settings.homepage', []));

            foreach (array_keys($this->input('content', [])) as $key) {
                if (! in_array($key, $allowedKeys, true)) {
                    $validator->errors()->add("content.{$key}", "\"{$key}\" is not a recognized homepage content key.");
                }
            }
        });
    }
}
