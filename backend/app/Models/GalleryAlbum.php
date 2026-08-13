<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Standalone photo/video album (Database Design, Section 4.6). Items
 * attach via one polymorphic collection ('items') that holds both
 * images and videos — there's no separate items table (see the
 * migration's docblock) and no separate video collection; type is
 * distinguished at read time from each Media row's mime_type.
 */
class GalleryAlbum extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = ['title', 'slug', 'description', 'order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('items');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Spatie silently skips conversions for non-image files, so this
        // is safe to register even though the collection also holds
        // videos.
        $this->addMediaConversion('thumb')->keepOriginalImageFormat()->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->keepOriginalImageFormat()->width(1920)->optimize()->nonQueued();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
