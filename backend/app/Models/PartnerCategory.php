<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Groups Partners by type (Milestone 16). Flat taxonomy — see the
 * migration's docblock for why this mirrors BlogCategory/NewsCategory
 * rather than CourseCategory's hierarchy.
 */
class PartnerCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'category_id');
    }
}
