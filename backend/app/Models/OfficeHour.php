<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per day of the week (Settings module extension — see the
 * migration's docblock). Always exactly 7 rows, seeded once by
 * OfficeHourSeeder; the admin UI edits them in place rather than
 * creating/deleting rows.
 */
class OfficeHour extends Model
{
    use HasAuditColumns, HasFactory;

    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    protected $fillable = [
        'day',
        'is_open',
        'opens_at',
        'closes_at',
        'note',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'opens_at' => 'datetime:H:i',
            'closes_at' => 'datetime:H:i',
        ];
    }
}
