<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * News article/press release (Database Design, Section 4.6) —
 * "structurally identical" to BlogPost. Media attaches via two
 * polymorphic collections (featured_image, gallery); SEO via the shared
 * `seo_meta` table. Unlike BlogPost, no tags relation and no computed
 * "Related" set — neither was a requested feature for this milestone
 * (the shared `taggables` pivot already supports adding tags later
 * without a schema change, if News ever needs it).
 */
class News extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, HasSeoMeta, InteractsWithMedia, SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'author_id',
        'status',
        'published_at',
        'is_featured',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->width(1920)->optimize()->nonQueued();
    }

    /**
     * status='published' is the normal case; status='scheduled' with a
     * past published_at is a defensive fallback for the brief window
     * before PublishScheduledNews (FR-37) next runs and flips it over —
     * same pattern as BlogPost::scopePublished().
     */
    public function scopePublished(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where(function (Builder $q) use ($now) {
            $q->where('status', 'published')
                ->orWhere(function (Builder $q2) use ($now) {
                    $q2->where('status', 'scheduled')->where('published_at', '<=', $now);
                });
        });
    }
}
