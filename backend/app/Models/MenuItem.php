<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Individual navigation entry (Database Design, Section 4.5), nestable
 * to arbitrary depth via parent_id. See the migration's docblock for the
 * Menu Builder hardening fields (description/icon/is_mega_menu,
 * starts_at/ends_at, visible_on) beyond the original Database Design.
 */
class MenuItem extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'linkable_type',
        'linkable_id',
        'custom_url',
        'description',
        'icon',
        'is_mega_menu',
        'open_in_new_tab',
        'order',
        'is_active',
        'starts_at',
        'ends_at',
        'visible_on',
    ];

    protected function casts(): array
    {
        return [
            'is_mega_menu' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Visible right now, ignoring is_active/device targeting — just the
     * scheduling window (mirrors hero_slides' starts_at/ends_at pattern).
     */
    public function scopeCurrentlyScheduled(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    /**
     * Resolves the actual href for this item: custom_url as-is, or an
     * internal link resolved through config('menus.linkable_types')'s
     * per-type resolver. That allow-list is empty until a future
     * milestone registers its first content type (Page, Course, ...) —
     * until then every item in practice is a custom_url link, and an
     * internal-linked item (which can't be created yet — see
     * MenuItemRequest) would correctly resolve to null rather than a
     * broken link.
     */
    public function resolveUrl(): ?string
    {
        if ($this->custom_url) {
            return $this->custom_url;
        }

        if (! $this->linkable_type) {
            return null;
        }

        $resolver = config("menus.linkable_types.{$this->linkable_type}.resolve_url");

        return $resolver ? $resolver($this->linkable_id) : null;
    }
}
