<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Database Design, Section 4.8 — serves both per-entity FAQs (Courses,
 * Events) and the global Site FAQ (faqable_type/id both null). The
 * Course-scoped path shipped in Course Management; Milestone 17 wires up
 * the global Site FAQ path (category_id, search) — see FaqController.
 * category_id only ever applies to the global path (course-scoped FAQs
 * have no category concept).
 */
class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'faqable_type',
        'faqable_id',
        'category_id',
        'question',
        'answer',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** The global Site FAQ path — rows with no owning entity. */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('faqable_type')->whereNull('faqable_id');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('question', 'like', "%{$term}%")
            ->orWhere('answer', 'like', "%{$term}%"));
    }
}
