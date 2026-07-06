<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $featuredImage = $this->getFirstMedia('featured_image');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'category' => $this->whenLoaded('category', fn () => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name, 'slug' => $this->category->slug] : null),
            'author' => $this->whenLoaded('author', fn () => $this->author ? ['id' => $this->author->id, 'name' => $this->author->name] : null),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'status' => $this->status,
            'published_at' => $this->published_at,
            'is_featured' => $this->is_featured,
            'views_count' => $this->views_count,
            'featured_image_url' => $featuredImage?->hasGeneratedConversion('web') ? $featuredImage->getUrl('web') : $featuredImage?->getUrl(),
            'gallery' => $this->getMedia('gallery')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])->values(),
            'related_posts' => BlogPostSummaryResource::collection($this->whenLoaded('relatedPosts')),
            'seo' => $this->whenLoaded('seoMeta', fn () => $this->seoMeta ? new SeoMetaResource($this->seoMeta) : null),
            'created_at' => $this->created_at,
        ];
    }
}
