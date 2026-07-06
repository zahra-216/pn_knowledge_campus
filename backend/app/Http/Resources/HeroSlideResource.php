<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroSlideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->getFirstMedia('slide_image');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'cta_text' => $this->cta_text,
            'cta_url' => $this->cta_url,
            'order' => $this->order,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_active' => $this->is_active,
            'image_url' => $media?->hasGeneratedConversion('web') ? $media->getUrl('web') : $media?->getUrl(),
            'thumb_url' => $media?->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null,
        ];
    }
}
