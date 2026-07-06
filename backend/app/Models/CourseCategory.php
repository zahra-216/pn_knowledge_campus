<?php

namespace App\Models;

use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Course taxonomy term — not in the Database Design document (added
 * mirroring CourseLevel/CourseMode, see the original migration's
 * docblock), now promoted beyond a flat lookup: self-referencing
 * `parent_id` for a Category > Subcategory tree, plus icon/image media
 * and SEO (via config('seo.seoable_types')). No public detail page of
 * its own, so unlike Faculty/Course/etc. its Resource doesn't embed
 * `seo` — only the admin SEO tab (GET/PUT /admin/seo/course-category/{id})
 * uses this relation.
 */
class CourseCategory extends Model implements HasMedia
{
    use HasFactory, HasSeoMeta, InteractsWithMedia;

    protected $fillable = ['name', 'slug', 'order', 'parent_id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile();
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->width(1920)->optimize()->nonQueued();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
