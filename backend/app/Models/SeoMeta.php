<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Shared SEO field set for any seoable content type (Database Design,
 * Section 4.10), attached via a polymorphic (seoable_type, seoable_id)
 * pair. Infrastructure only in Milestone 1 — the SeoMetaController is
 * upsert-only, and `seoable_type` is validated against
 * config('seo.seoable_types'), which starts empty and gains one entry
 * per content model as each later milestone ships it.
 */
class SeoMeta extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'seo_meta';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'seo_title',
        'meta_description',
        'keywords',
        'canonical_url',
        'schema_type',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
        'robots_index',
        'robots_follow',
    ];

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
