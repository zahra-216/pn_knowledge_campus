<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->getFirstMedia('logo');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'order' => $this->order,
            'is_active' => $this->is_active,
            'logo_url' => $media?->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media?->getUrl(),
        ];
    }
}
