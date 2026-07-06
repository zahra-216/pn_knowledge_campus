<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Physical campus branch/location (Database Design, Section 4.2).
 * Standalone — feeds the public Contact page and footer.
 */
class Branch extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'email',
        'latitude',
        'longitude',
        'is_head_office',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
