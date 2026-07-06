<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lightweight shape for "Related Posts" — enough for a teaser card, not
 * the full BlogPostResource (no body, tags, or SEO).
 */
class BlogPostSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $featuredImage = $this->getFirstMedia('featured_image');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'category' => $this->whenLoaded('category', fn () => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null),
            'featured_image_url' => $featuredImage?->hasGeneratedConversion('thumb') ? $featuredImage->getUrl('thumb') : $featuredImage?->getUrl(),
            'published_at' => $this->published_at,
        ];
    }
}
