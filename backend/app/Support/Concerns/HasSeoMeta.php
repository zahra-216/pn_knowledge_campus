<?php

namespace App\Support\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Every model registered in config('seo.seoable_types') gets this
 * relation so its own public detail Resource can embed SEO fields
 * directly (see e.g. FacultyResource) — until the Public Website
 * milestone, SEO was reachable only through the admin-gated
 * GET /admin/seo/{type}/{id} endpoint, meaning the public site had no
 * way to read a page's own meta title/description/OG/canonical/schema
 * data at all. This trait doesn't change that admin endpoint; it just
 * gives each model a named relation to eager-load and embed.
 *
 * Usage:
 *   use App\Support\Concerns\HasSeoMeta;
 *   class Faculty extends Model { use HasSeoMeta; }
 */
trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
