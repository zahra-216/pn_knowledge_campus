<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** The full admin shape (Milestone 20) — includes review tracking fields the applicant never sees (see ApplicationPublicResource). */
class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
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
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn () => $this->reviewedBy ? [
                'id' => $this->reviewedBy->id,
                'name' => $this->reviewedBy->name,
            ] : null),
            'reviewed_at' => $this->reviewed_at,
            'review_notes' => $this->review_notes,
            'documents' => ApplicationDocumentResource::collection(
                $this->getMedia('documents')->each(fn ($media) => $media->application_number = $this->application_number)
            ),
            'created_at' => $this->created_at,
        ];
    }
}
