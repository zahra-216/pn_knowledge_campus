<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single ordered content block on a Page (Database Design, Section
 * 4.5). `data`'s shape depends on `block_type` — validated per-type by
 * PageBlockRequest, not by the database (see the migration's docblock).
 */
class PageBlock extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = [
        'page_id',
        'block_type',
        'data',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
