<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Audit fix (High remediation) — see the migration's docblock. Reuses
 * one Download catalog entry (e.g. a Prospectus PDF) across multiple
 * unrelated content records (a Course page, a static Page) without
 * duplicating the file.
 */
class DownloadAttachment extends Model
{
    protected $fillable = [
        'download_id',
        'attachable_type',
        'attachable_id',
    ];

    public function download(): BelongsTo
    {
        return $this->belongsTo(Download::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
