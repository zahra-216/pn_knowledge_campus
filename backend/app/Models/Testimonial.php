<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Student/alumni testimonial (Database Design, Section 4.5), optionally
 * tied to a Course. is_featured drives inclusion in the homepage's
 * Testimonials section; a testimonial can exist without being featured
 * (e.g. shown only on a Course detail page).
 */
class Testimonial extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'role_title',
        'course_id',
        'content',
        'rating',
        'is_featured',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'rating' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->keepOriginalImageFormat()->width(200)->height(200)->sharpen(10)->optimize()->nonQueued();
    }
}
