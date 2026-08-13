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
            'assigned_to' => $this->whenLoaded('assignedTo', fn () => $this->assignedTo ? [
                'id' => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
            ] : null),
            // Explicit [] default rather than whenLoaded()'s bare
            // omit-the-key-entirely behavior (audit fix, Critical
            // remediation) — the admin list endpoint doesn't eager-load
            // `notes`, only the detail endpoint does, and the frontend's
            // `notes: InquiryNote[]` type assumed the key was always
            // present. Omitting it on the list response meant any code
            // path that set its "active inquiry" state straight from a
            // list row instead of fetching detail crashed on
            // `.notes.length`.
            'notes' => $this->whenLoaded('notes', fn () => InquiryNoteResource::collection($this->notes), []),
            'created_at' => $this->created_at,
        ];
    }
}
