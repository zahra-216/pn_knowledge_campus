<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Not part of the Database Design document — a bridge for a real gap in
 * it (see the migration's docblock for the full rationale). This is a
 * single-row singleton (id=1, seeded by its own migration) that owns
 * every Media Library upload until a later milestone's real content
 * model (Course, Page, News, ...) claims it via $media->move().
 *
 * Every upload in this milestone lands in the 'library' collection here;
 * there is deliberately no other collection name used against this model.
 */
class MediaLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'media_library';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // ->optimize() runs spatie/image-optimizer's chain (jpegoptim,
        // pngquant, optipng, svgo, gifsicle) — already a dependency. Each
        // optimizer silently no-ops if its binary isn't installed on the
        // host, so this is always safe to call, never a hard requirement.
        $this->addMediaConversion('thumb')
            ->keepOriginalImageFormat()
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->optimize()
            ->nonQueued();

        $this->addMediaConversion('web')
            ->keepOriginalImageFormat()
            ->width(1600)
            ->optimize()
            ->nonQueued();
    }
}
