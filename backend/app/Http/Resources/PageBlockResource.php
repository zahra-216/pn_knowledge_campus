<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'block_type' => $this->block_type,
            'data' => $this->data,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];
    }
}
