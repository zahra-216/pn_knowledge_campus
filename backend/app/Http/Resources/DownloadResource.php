<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DownloadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->getFirstMedia('file');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'file_url' => $media?->getUrl(),
            'file_name' => $media?->file_name,
            'file_size' => $media?->size,
            'file_type' => $media?->mime_type,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];
    }
}
