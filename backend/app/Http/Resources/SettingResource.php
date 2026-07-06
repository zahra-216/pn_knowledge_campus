<?php

namespace App\Http\Resources;

use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => Settings::cast($this->key, $this->value),
            'group' => $this->group,
            'is_public' => $this->is_public,
        ];
    }
}
