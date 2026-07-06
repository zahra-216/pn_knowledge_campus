<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseCurriculumItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'order' => $this->order,
            'children' => CourseCurriculumItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
