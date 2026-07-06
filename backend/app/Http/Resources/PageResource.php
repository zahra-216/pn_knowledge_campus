<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'template' => $this->template,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'blocks' => PageBlockResource::collection($this->whenLoaded('blocks')),
            'seo' => $this->whenLoaded('seoMeta', fn () => $this->seoMeta ? new SeoMetaResource($this->seoMeta) : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
