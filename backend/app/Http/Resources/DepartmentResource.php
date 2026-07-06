<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $banner = $this->getFirstMedia('banner');

        return [
            'id' => $this->id,
            'faculty_id' => $this->faculty_id,
            'faculty' => $this->whenLoaded('faculty', fn () => [
                'id' => $this->faculty->id,
                'name' => $this->faculty->name,
                'slug' => $this->faculty->slug,
            ]),
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status,
            'banner_url' => $banner?->hasGeneratedConversion('web') ? $banner->getUrl('web') : $banner?->getUrl(),
            'courses' => $this->relationLoaded('courses')
                ? $this->courses->map(fn ($course) => [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'slug' => $course->slug,
                    'overview' => $course->overview,
                    'status' => $course->status,
                ])->values()
                : [],
            'seo' => $this->whenLoaded('seoMeta', fn () => $this->seoMeta ? new SeoMetaResource($this->seoMeta) : null),
            'created_at' => $this->created_at,
        ];
    }
}
