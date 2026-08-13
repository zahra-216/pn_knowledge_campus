<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The standalone Downloads catalog (Milestone 18) — Prospectus,
 * Application Forms, Brochures, and other public documents. One file
 * per row via Spatie's polymorphic 'file' collection, same shape as
 * Partner's single 'logo' collection. Distinct from Course's own
 * `downloads` media collection (untouched by this milestone).
 *
 * `requires_inquiry`/`download_count`/`attachments` are an audit fix
 * (High remediation) — see their migrations' docblocks for what they
 * complete (FR-06's gated-download capture flow and the Database
 * Design document's cross-entity reuse pivot, respectively).
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
        'requires_inquiry',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_inquiry' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DownloadCategory::class, 'category_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DownloadAttachment::class);
    }

    /**
     * `requires_inquiry` downloads use the 'local' disk (no symlink, no
     * public URL — same rationale as Application::registerMediaCollections())
     * so the gate can't be bypassed by guessing the storage/public path;
     * ungated downloads stay on 'public' for a plain, cacheable direct link.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')
            ->useDisk($this->requires_inquiry ? 'local' : 'public')
            ->singleFile();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
