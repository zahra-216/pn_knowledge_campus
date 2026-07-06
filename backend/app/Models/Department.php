<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Belongs to exactly one Faculty, groups Courses (Database Design,
 * Section 4.4). `courses()` is a real relation now (Course Management).
 */
class Department extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, HasSeoMeta, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'name',
        'slug',
        'short_description',
        'description',
        'order',
        'status',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->sharpen(10)->optimize()->nonQueued();
        $this->addMediaConversion('web')->width(1920)->optimize()->nonQueued();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
