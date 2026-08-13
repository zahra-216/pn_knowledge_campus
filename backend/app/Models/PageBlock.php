<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\RichText;
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

    /**
     * Audit fix (High remediation) — see App\Support\RichText's docblock.
     * Only the `rich_text` block type carries raw editor HTML; every
     * other block type's `data` is either plain strings or ids, nothing
     * to sanitize.
     */
    protected static function booted(): void
    {
        static::saving(function (self $block) {
            if ($block->block_type === 'rich_text' && isset($block->data['body'])) {
                $data = $block->data;
                $data['body'] = RichText::sanitize($data['body']);
                $block->data = $data;
            }
        });
    }
}
