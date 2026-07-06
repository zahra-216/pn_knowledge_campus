<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shared shape for CourseLevel, CourseMode, and CourseCategory — all
 * three are the same {name, slug, order} lookup structure (Database
 * Design, Section 4.3's stated reason for normalizing Level/Mode, and
 * Category mirrors it).
 */
class CourseLookupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'order' => $this->order,
        ];
    }
}
