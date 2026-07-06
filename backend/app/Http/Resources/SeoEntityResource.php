<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * SEO Manager's per-type drill-down list (SeoMetaController::typeIndex).
 * Shared across every seoable type — `seo_manager_label` is set on each
 * model in-memory by the controller (see its docblock) to paper over
 * 'name' vs 'title' vs 'course_name' varying per model.
 */
class SeoEntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->seo_manager_label,
            'slug' => $this->slug,
            'has_seo' => (bool) $this->seoMeta,
            'seo_title' => $this->seoMeta?->seo_title,
            'robots_index' => $this->seoMeta?->robots_index ?? true,
        ];
    }
}
