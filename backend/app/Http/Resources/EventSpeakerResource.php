<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSpeakerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photo = $this->getFirstMedia('photo');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'bio' => $this->bio,
            'order' => $this->order,
            'photo_url' => $photo?->hasGeneratedConversion('thumb') ? $photo->getUrl('thumb') : $photo?->getUrl(),
        ];
    }
}
