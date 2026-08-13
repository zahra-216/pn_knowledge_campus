<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Static/informational page composed via the Page Builder (Database
 * Design, Section 4.5) — About, Vision, Mission, Chairman's Message,
 * Admissions, International Students, Student Life, Career, and the
 * legal pages. Content lives entirely in the ordered `blocks` relation;
 * this model only carries routing/publishing metadata.
 *
 * SoftDeletes (audit fix, Medium remediation) — every other content
 * model already has this per Database Design, Section 2.1's blanket
 * rule; Pages was the one exception.
 */
class Page extends Model
{
    use HasAuditColumns, HasFactory, HasSeoMeta, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'template',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('order');
    }

    /**
     * status='published' is the normal case; status='scheduled' with a
     * past published_at is a defensive fallback for the brief window
     * before PublishScheduledPages (FR-37) next runs and flips it over.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where(function (Builder $q) use ($now) {
            $q->where('status', 'published')
                ->orWhere(function (Builder $q2) use ($now) {
                    $q2->where('status', 'scheduled')->where('published_at', '<=', $now);
                });
        });
    }
}
