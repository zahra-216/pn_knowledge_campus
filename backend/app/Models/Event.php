<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Campus event (Database Design, Section 4.6) — "open days, seminars,
 * graduations." Media attaches via two polymorphic collections
 * (featured_image, gallery); SEO via the shared `seo_meta` table.
 * `speakers` is a client-requested addition beyond the base schema (see
 * the event_speakers migration's docblock).
 */
class Event extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, HasSeoMeta, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'venue',
        'is_online',
        'starts_at',
        'ends_at',
        'description',
        'registration_url',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(EventSpeaker::class)->orderBy('order');
    }

    /**
     * Audit fix (Medium remediation) — the shared `faqs` table's own
     * docblock already documented Event as a supported faqable type
     * (Course::faqs() being the only one actually wired up); this
     * closes that specific gap. A dedicated nested-route admin
     * CRUD/UI matching CourseFaqController's is a larger, separate
     * addition — deliberately out of scope here (see that
     * controller's own docblock: Events was always its own later step).
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->keepOriginalImageFormat()->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->keepOriginalImageFormat()->width(1920)->optimize()->nonQueued();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * "List published events. filter[upcoming]=1 (default) or
     * filter[past]=1, sorted by starts_at" (API Design, Section 7.3).
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', Carbon::now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('starts_at', '<', Carbon::now());
    }
}
