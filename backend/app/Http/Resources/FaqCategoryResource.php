<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'order' => $this->order,
            'faqs_count' => $this->when(isset($this->faqs_count), fn () => (int) $this->faqs_count),
        ];
    }
}
