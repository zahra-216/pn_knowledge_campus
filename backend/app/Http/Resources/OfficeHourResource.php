<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'day' => $this->day,
            'is_open' => $this->is_open,
            'opens_at' => $this->opens_at?->format('H:i'),
            'closes_at' => $this->closes_at?->format('H:i'),
            'note' => $this->note,
            'order' => $this->order,
        ];
    }
}
