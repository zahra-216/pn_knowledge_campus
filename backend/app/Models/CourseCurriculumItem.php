<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Structured curriculum module/lesson (Database Design, Section 4.3) —
 * self-referencing via parent_id (null = top-level module, set = a
 * lesson under a module). The Phase 2 LMS attachment point.
 */
class CourseCurriculumItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'parent_id',
        'title',
        'description',
        'duration',
        'order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }
}
