<?php

namespace App\Http\Requests\Seo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/admin/seo/{type}/{id} (API Design, Section 8.8) — upsert.
 * The {type}/{id} allow-list check (config/seo.php) happens in the
 * controller, not here, since it needs the route parameters rather than
 * the request body.
 */
class UpdateSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by seo.edit in the controller
    }

    public function rules(): array
    {
        return [
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'schema_type' => ['nullable', 'string', 'max:60'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'twitter_title' => ['nullable', 'string', 'max:160'],
            'twitter_description' => ['nullable', 'string', 'max:320'],
            'twitter_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'robots_index' => ['boolean'],
            'robots_follow' => ['boolean'],
        ];
    }
}
