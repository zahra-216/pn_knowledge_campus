<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One item inside a gallery album's 'items' media collection — type is
 * derived from mime_type since images and videos share one collection
 * (Database Design, Section 4.6); caption comes from Spatie's per-file
 * custom_properties, not a database column.
 */
class GalleryMediaItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => str_starts_with((string) $this->mime_type, 'video/') ? 'video' : 'image',
            'mime_type' => $this->mime_type,
            'url' => $this->getUrl(),
            'thumb_url' => $this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : null,
            'caption' => $this->getCustomProperty('caption'),
            'order' => $this->order_column,
        ];
    }
}
