<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * News article/press release (Database Design, Section 4.6) —
 * "structurally identical" to BlogPost. Media attaches via two
 * polymorphic collections (featured_image, gallery); SEO via the shared
 * `seo_meta` table.
 *
 * `tags()` (audit fix, Medium remediation) — the shared `taggables`
 * pivot always supported this without a schema change; only the
 * relation itself was missing. A full sync/filter/embed flow matching
 * BlogPostController's (tag-name resolution on save, ?tag= filtering,
 * TagResource embedding) is a larger, separate addition — deliberately
 * out of scope here. No computed "Related" set either, still unrequested.
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

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
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

    /** Audit fix (High remediation) — see App\Support\RichText's docblock. */
    protected static function booted(): void
    {
        static::saving(function (self $news) {
            $news->body = RichText::sanitize($news->body);
        });
    }
}
