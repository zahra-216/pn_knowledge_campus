<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lookup table for delivery mode (Database Design, Section 4.3) — e.g.
 * Full-Time, Part-Time, Online, Blended.
 */
class CourseMode extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'mode_id');
    }
}
