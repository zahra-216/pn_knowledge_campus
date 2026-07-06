<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The standalone Downloads catalog (Milestone 18) — Prospectus,
 * Application Forms, Brochures, and other public documents. One file
 * per row via Spatie's polymorphic 'file' collection, same shape as
 * Partner's single 'logo' collection. Distinct from Course's own
 * `downloads` media collection (untouched by this milestone).
 */
class Download extends Model implements HasMedia
{
    use HasAuditColumns, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DownloadCategory::class, 'category_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
