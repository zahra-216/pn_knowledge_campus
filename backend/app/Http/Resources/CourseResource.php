<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Matches the API Design's Section 9.4 example response shape: nested
 * faculty/department/level/mode summary objects, a computed `duration`
 * display string, and a nested `price` object (amount/currency/
 * discount_amount — discount is a client-requested addition beyond the
 * document's own example).
 */
class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $featuredImage = $this->getFirstMedia('featured_image');

        return [
            'id' => $this->id,
            'course_name' => $this->course_name,
            'course_code' => $this->course_code,
            'slug' => $this->slug,
            'faculty' => $this->whenLoaded('faculty', fn () => [
                'id' => $this->faculty->id,
                'name' => $this->faculty->name,
                'slug' => $this->faculty->slug,
            ]),
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'slug' => $this->department->slug,
            ]),
            'level' => $this->whenLoaded('level', fn () => ['id' => $this->level->id, 'name' => $this->level->name]),
            'mode' => $this->whenLoaded('mode', fn () => ['id' => $this->mode->id, 'name' => $this->mode->name]),
            'category' => $this->whenLoaded('category', fn () => $this->category ? (new CourseCategoryResource($this->category))->resolve() : null),
            'duration_value' => $this->duration_value,
            'duration_unit' => $this->duration_unit,
            'duration' => $this->duration_label,
            'price' => [
                'amount' => $this->price !== null ? (float) $this->price : null,
                'currency' => $this->price_currency,
                'discount_amount' => $this->discount_price !== null ? (float) $this->discount_price : null,
            ],
            'overview' => $this->overview,
            'description' => $this->description,
            'entry_requirements' => $this->entry_requirements,
            'learning_outcomes' => $this->learning_outcomes,
            'career_opportunities' => $this->career_opportunities,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'is_featured' => $this->is_featured,
            'order' => $this->order,
            'featured_image_url' => $featuredImage?->hasGeneratedConversion('web') ? $featuredImage->getUrl('web') : $featuredImage?->getUrl(),
            'gallery' => $this->getMedia('gallery')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])->values(),
            'downloads' => $this->getMedia('downloads')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl(),
                'size' => $media->size,
            ])->values(),
            'curriculum' => CourseCurriculumItemResource::collection($this->whenLoaded('topLevelCurriculumItems')),
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
            'seo' => $this->whenLoaded('seoMeta', fn () => $this->seoMeta ? new SeoMetaResource($this->seoMeta) : null),
            'created_at' => $this->created_at,
        ];
    }
}
