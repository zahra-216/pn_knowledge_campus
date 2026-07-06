<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
 * Blog article (Database Design, Section 4.6) — "structurally identical
 * to News by design." Media attaches via two polymorphic collections
 * (featured_image, gallery); tags via the shared `taggables` pivot; SEO
 * via the shared `seo_meta` table.
 */
class BlogPost extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, HasSeoMeta, InteractsWithMedia, SoftDeletes;

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
        return $this->belongsTo(BlogCategory::class, 'category_id');
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
        $this->addMediaConversion('thumb')->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->width(1920)->optimize()->nonQueued();
    }

    /**
     * status='published' is the normal case; status='scheduled' with a
     * past published_at is a defensive fallback for the brief window
     * before PublishScheduledBlogPosts (FR-37) next runs and flips it
     * over — same pattern as Page::scopePubliclyVisible().
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

    /**
     * "Related Posts" — not a stored relation (not in the Database
     * Design document), computed on read: posts sharing this post's
     * category first, topped up with posts sharing a tag if the
     * category alone doesn't fill $limit. Published only, excludes self.
     */
    public function findRelated(int $limit = 3): Collection
    {
        $base = static::published()->where('id', '!=', $this->id);

        $sameCategory = (clone $base)
            ->when($this->category_id, fn (Builder $q) => $q->where('category_id', $this->category_id), fn (Builder $q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        $remaining = $limit - $sameCategory->count();

        if ($remaining <= 0 || $this->tags->isEmpty()) {
            return $sameCategory->values();
        }

        $byTag = (clone $base)
            ->whereNotIn('id', $sameCategory->pluck('id'))
            ->whereHas('tags', fn (Builder $q) => $q->whereIn('tags.id', $this->tags->pluck('id')))
            ->orderByDesc('published_at')
            ->limit($remaining)
            ->get();

        return $sameCategory->concat($byTag)->values();
    }
}
