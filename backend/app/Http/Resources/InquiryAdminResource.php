<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin inbox shape — everything InquiryResource (the public store
 * confirmation) deliberately omits. Kept as a separate class rather
 * than adding an admin-only conditional to InquiryResource, matching
 * the ApplicationResource/ApplicationPublicResource split already used
 * for the same "visitor gets a thin confirmation, staff get the full
 * record" shape.
 */
class InquiryAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'source_page' => $this->source_page,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'name' => $this->course->course_name,
                'slug' => $this->course->slug,
            ]),
            'international_applicant' => $this->international_applicant,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
