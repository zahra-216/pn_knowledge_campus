<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoMetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seoable_type' => $this->seoable_type,
            'seoable_id' => $this->seoable_id,
            'seo_title' => $this->seo_title,
            'meta_description' => $this->meta_description,
            'keywords' => $this->keywords,
            'canonical_url' => $this->canonical_url,
            'schema_type' => $this->schema_type,
            'og_title' => $this->og_title,
            'og_description' => $this->og_description,
            // Bare Media Library ids, resolved client-side via
            // useResolvedMedia() — same convention as Settings'
            // logo_media_id/favicon_media_id and PageBlock's inline
            // image fields (see GET /api/v1/media/resolve's docblock).
            'og_image_media_id' => $this->og_image_media_id,
            'twitter_title' => $this->twitter_title,
            'twitter_description' => $this->twitter_description,
            'twitter_image_media_id' => $this->twitter_image_media_id,
            'robots_index' => $this->robots_index,
            'robots_follow' => $this->robots_follow,
        ];
    }
}
