<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Global key/value configuration registry (Database Design, Section 4.2).
 * `group` and `is_public` are fixed at seed time from config/settings.php
 * and are never touched by the bulk-update endpoint — only `value` is
 * ever written after the row is seeded (see SettingController::update).
 */
class Setting extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
