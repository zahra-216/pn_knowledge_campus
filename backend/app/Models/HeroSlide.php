<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Homepage/page banner slide with scheduling (Database Design, Section
 * 4.5). Image attaches directly via Spatie's polymorphic 'slide_image'
 * collection — the first real consumer of Media::moveKeepingCustomFields()
 * built in the Media Library milestone for exactly this "a real content
 * model claims a Media Library asset" scenario.
 */
class HeroSlide extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'subtitle',
        'cta_text',
        'cta_url',
        'order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('slide_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->width(1920)->optimize()->nonQueued();
    }

    /**
     * Visible right now, ignoring is_active — mirrors MenuItem's
     * scopeCurrentlyScheduled (same starts_at/ends_at window pattern).
     */
    public function scopeCurrentlyScheduled(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }
}
