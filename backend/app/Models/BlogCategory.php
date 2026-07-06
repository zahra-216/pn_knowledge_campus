<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Blog taxonomy term (Database Design, Section 4.6) — kept separate from
 * a future News category table by design (see the migration's docblock).
 */
class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}
