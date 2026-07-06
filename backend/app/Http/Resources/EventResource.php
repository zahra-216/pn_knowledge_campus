<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $featuredImage = $this->getFirstMedia('featured_image');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'venue' => $this->venue,
            'is_online' => $this->is_online,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_upcoming' => $this->starts_at?->greaterThanOrEqualTo(Carbon::now()) ?? false,
            'description' => $this->description,
            'registration_url' => $this->registration_url,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'featured_image_url' => $featuredImage?->hasGeneratedConversion('web') ? $featuredImage->getUrl('web') : $featuredImage?->getUrl(),
            'gallery' => $this->getMedia('gallery')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])->values(),
            'speakers' => EventSpeakerResource::collection($this->whenLoaded('speakers')),
            'seo' => $this->whenLoaded('seoMeta', fn () => $this->seoMeta ? new SeoMetaResource($this->seoMeta) : null),
            'created_at' => $this->created_at,
        ];
    }
}
