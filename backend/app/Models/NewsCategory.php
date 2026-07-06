<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * News taxonomy term (Database Design, Section 4.6) — kept separate
 * from BlogCategory by design (see the migration's docblock).
 */
class NewsCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'category_id');
    }
}
