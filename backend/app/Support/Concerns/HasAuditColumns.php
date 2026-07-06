<?php

namespace App\Support\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Every editable content table in the Database Design document carries
 * created_by / updated_by columns (nullable, FK users.id, ON DELETE SET
 * NULL) to satisfy the audit-trail requirement (SRS FR-38). Rather than
 * repeating this on every future model (Course, Page, News, ...), it lives
 * here once and every content model uses this trait.
 *
 * Usage on a model:
 *   use App\Support\Concerns\HasAuditColumns;
 *   class Course extends Model { use HasAuditColumns; }
 *
 * The migration for that model must include:
 *   $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
 *   $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
 */
trait HasAuditColumns
{
    protected static function bootHasAuditColumns(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && ! $model->isDirty('created_by')) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
