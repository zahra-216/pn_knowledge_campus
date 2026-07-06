<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The applicant-facing shape (Milestone 20) — omits `review_notes` and
 * `reviewed_by` (internal staff commentary/identity is not the
 * applicant's business) while still surfacing `status` so they can
 * track progress. See ApplicationResource for the full admin shape.
 */
class ApplicationPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'application_number' => $this->application_number,
            'course' => $this->whenLoaded('course', fn () => $this->course ? [
                'id' => $this->course->id,
                'name' => $this->course->course_name,
                'slug' => $this->course->slug,
            ] : null),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'nationality' => $this->nationality,
            'address' => $this->address,
            'international_applicant' => $this->international_applicant,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'documents' => ApplicationDocumentResource::collection(
                $this->getMedia('documents')->each(fn ($media) => $media->application_number = $this->application_number)
            ),
            'created_at' => $this->created_at,
        ];
    }
}
