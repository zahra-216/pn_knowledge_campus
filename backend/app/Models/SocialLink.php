<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Social media link shown in the header/footer (Database Design,
 * Section 4.2).
 */
class SocialLink extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = [
        'platform',
        'url',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
