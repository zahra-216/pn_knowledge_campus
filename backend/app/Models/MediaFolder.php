<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Self-referencing folder tree for Media Library organization (Database
 * Design, Section 4.3). Deliberately does NOT use HasAuditColumns — this
 * table only tracks `created_by`, not `updated_by`, per the Database
 * Design's explicit field list for this table (a documented exception to
 * the Section 2.1 default).
 */
class MediaFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaFolder $folder) {
            if (Auth::check() && ! $folder->isDirty('created_by')) {
                $folder->created_by = Auth::id();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
