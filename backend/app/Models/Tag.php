<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Shared tag vocabulary (Database Design, Section 4.6) — usable across
 * News and Blog Posts via the polymorphic `taggables` pivot.
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function blogPosts(): MorphToMany
    {
        return $this->morphedByMany(BlogPost::class, 'taggable');
    }

    /** Audit fix (Medium remediation) — symmetric with News::tags(). */
    public function news(): MorphToMany
    {
        return $this->morphedByMany(News::class, 'taggable');
    }
}
