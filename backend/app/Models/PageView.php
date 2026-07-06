<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single public page visit (Milestone 24) — see the migration's
 * docblock. Append-only log, no `updated_at` column (a page view is
 * never edited after the fact).
 */
class PageView extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['path', 'visitor_id'];
}
