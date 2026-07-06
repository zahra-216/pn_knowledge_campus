<?php

namespace App\Http\Requests\Settings;

use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/admin/settings (API Design, Section 8.9) — bulk update by
 * key. Body shape: { "settings": { "campus_name": "...", ... } }. Only
 * keys declared in config/settings.php may be written; group and
 * is_public are never accepted here (SettingController fixes them from
 * the registry), so a client can never expose a secret key by tampering
 * with those flags.
 */
class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by SettingPolicy in the controller
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1'],
            'settings.*' => ['nullable', 'string', 'max:65535'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach (array_keys($this->input('settings', [])) as $key) {
                if (! Settings::isValidKey($key)) {
                    $validator->errors()->add("settings.{$key}", "\"{$key}\" is not a recognized setting key.");
                }
            }
        });
    }
}
