<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lookup table for course levels (Database Design, Section 4.3) — e.g.
 * Certificate, Diploma, Undergraduate, Postgraduate.
 */
class CourseLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'level_id');
    }
}
