<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $icon = $this->getFirstMedia('icon');
        $image = $this->getFirstMedia('image');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'order' => $this->order,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? ['id' => $this->parent->id, 'name' => $this->parent->name] : null),
            'icon_url' => $icon?->getUrl(),
            'image_url' => $image?->hasGeneratedConversion('web') ? $image->getUrl('web') : $image?->getUrl(),
            'courses_count' => $this->when(isset($this->courses_count), fn () => (int) $this->courses_count),
            'children' => CourseCategoryResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
        ];
    }
}
