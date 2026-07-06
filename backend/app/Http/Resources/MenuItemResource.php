<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'label' => $this->label,
            'linkable_type' => $this->linkable_type,
            'linkable_id' => $this->linkable_id,
            'custom_url' => $this->custom_url,
            'url' => $this->resolveUrl(),
            'description' => $this->description,
            'icon' => $this->icon,
            'is_mega_menu' => $this->is_mega_menu,
            'open_in_new_tab' => $this->open_in_new_tab,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'visible_on' => $this->visible_on,
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
