<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Audit fix (Medium remediation) — the public course list used the same
 * CourseResource as the detail page, so every card in a 12-per-page
 * listing transferred and parsed the full description/entry_requirements/
 * learning_outcomes/career_opportunities body text plus the complete
 * gallery/downloads arrays (measured: ~2.1KB/item vs. a single detail
 * page at 3.7KB total). This mirrors what a course card actually
 * renders (ContentCard: image, title, faculty, overview, duration) —
 * everything else lives in CourseResource for the detail page only.
 */
class CourseListResource extends JsonResource
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
            'status' => $this->status,
            'published_at' => $this->published_at,
            'is_featured' => $this->is_featured,
            'order' => $this->order,
            'featured_image_url' => $featuredImage?->hasGeneratedConversion('web') ? $featuredImage->getUrl('web') : $featuredImage?->getUrl(),
            'created_at' => $this->created_at,
        ];
    }
}
