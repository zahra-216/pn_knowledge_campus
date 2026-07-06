<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Groups the global Site FAQ by topic (Milestone 17). Flat taxonomy —
 * see the migration's docblock for why this mirrors BlogCategory/
 * NewsCategory/PartnerCategory rather than CourseCategory's hierarchy.
 */
class FaqCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id');
    }
}
