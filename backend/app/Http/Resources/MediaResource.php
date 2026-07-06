<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'collection_name' => $this->collection_name,
            'folder_id' => $this->folder_id,
            'alt_text' => $this->alt_text,
            'url' => $this->getUrl(),
            'thumb_url' => $this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : null,
            'web_url' => $this->hasGeneratedConversion('web') ? $this->getUrl('web') : null,
            'width' => $this->getCustomProperty('width'),
            'height' => $this->getCustomProperty('height'),
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at,
        ];
    }
}
