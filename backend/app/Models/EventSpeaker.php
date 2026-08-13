<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Not in the Database Design document — see the migration's docblock.
 * A speaker belongs wholly to one event (cascade-deleted with it).
 */
class EventSpeaker extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['event_id', 'name', 'title', 'bio', 'order'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->keepOriginalImageFormat()->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
    }
}
