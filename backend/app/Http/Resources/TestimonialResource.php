<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->getFirstMedia('photo');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'role_title' => $this->role_title,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => $this->course ? [
                'id' => $this->course->id,
                'course_name' => $this->course->course_name,
                'slug' => $this->course->slug,
            ] : null),
            'content' => $this->content,
            'rating' => $this->rating,
            'is_featured' => $this->is_featured,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'photo_url' => $media?->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media?->getUrl(),
        ];
    }
}
