<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DownloadCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'order' => $this->order,
            'downloads_count' => $this->when(isset($this->downloads_count), fn () => (int) $this->downloads_count),
        ];
    }
}
