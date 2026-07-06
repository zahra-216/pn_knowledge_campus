<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacultyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $icon = $this->getFirstMedia('icon');
        $banner = $this->getFirstMedia('banner');
        $deanPhoto = $this->getFirstMedia('dean_photo');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'dean_name' => $this->dean_name,
            'dean_title' => $this->dean_title,
            'dean_message' => $this->dean_message,
            'order' => $this->order,
            'status' => $this->status,
            'icon_url' => $icon?->getUrl(),
            'banner_url' => $banner?->hasGeneratedConversion('web') ? $banner->getUrl('web') : $banner?->getUrl(),
            'dean_photo_url' => $deanPhoto?->hasGeneratedConversion('thumb') ? $deanPhoto->getUrl('thumb') : $deanPhoto?->getUrl(),
            'gallery' => $this->getMedia('gallery')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])->values(),
            'departments' => $this->relationLoaded('departments')
                ? $this->departments->map(fn ($department) => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'slug' => $department->slug,
                    'short_description' => $department->short_description,
                    'order' => $department->order,
                    'status' => $department->status,
                ])->values()
                : [],
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
