<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryAlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cover = $this->getFirstMedia('items');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'cover_url' => $cover?->hasGeneratedConversion('thumb') ? $cover->getUrl('thumb') : $cover?->getUrl(),
            'items_count' => $this->when(isset($this->items_count), fn () => (int) $this->items_count),
            'items' => GalleryMediaItemResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at,
        ];
    }
}
