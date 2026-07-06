<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * "The most structurally important table in Phase 1" (Database Design,
 * Section 4.3). Belongs to Faculty + Department (department must belong
 * to the same faculty — enforced in CourseRequest, not here). Media
 * attaches via three polymorphic collections (featured_image, gallery,
 * downloads) per the doc; curriculum and FAQs are real child
 * relations built alongside this milestone.
 */
class Course extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, HasSeoMeta, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'department_id',
        'level_id',
        'mode_id',
        'category_id',
        'course_name',
        'course_code',
        'slug',
        'duration_value',
        'duration_unit',
        'price',
        'discount_price',
        'price_currency',
        'overview',
        'description',
        'entry_requirements',
        'learning_outcomes',
        'career_opportunities',
        'status',
        'published_at',
        'is_featured',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'level_id');
    }

    public function mode(): BelongsTo
    {
        return $this->belongsTo(CourseMode::class, 'mode_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function curriculumItems(): HasMany
    {
        return $this->hasMany(CourseCurriculumItem::class)->orderBy('order');
    }

    public function topLevelCurriculumItems(): HasMany
    {
        return $this->curriculumItems()->whereNull('parent_id');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('downloads');
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

    /**
     * "3 Years" / "12 Weeks" — the computed display string the API
     * Design's example response shows in place of the raw
     * duration_value/duration_unit pair.
     */
    public function getDurationLabelAttribute(): string
    {
        $unit = ucfirst($this->duration_unit).($this->duration_value === 1 ? '' : 's');

        return "{$this->duration_value} {$unit}";
    }
}
