<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Groups the Downloads catalog by document type (Milestone 18). Flat
 * taxonomy — see the migration's docblock for why this mirrors
 * BlogCategory/PartnerCategory/FaqCategory rather than CourseCategory's
 * hierarchy.
 */
class DownloadCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'order'];

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class, 'category_id');
    }
}
