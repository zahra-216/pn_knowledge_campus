<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Slug-change protection (Database Design, Section 4.10). Table only in
 * Milestone 1 — no controller/routes yet; the UrlRedirectController and
 * its admin screen are Milestone 7 (Development Roadmap).
 */
class UrlRedirect extends Model
{
    use HasAuditColumns, HasFactory;

    protected $fillable = [
        'from_path',
        'to_path',
        'redirect_type',
        'hit_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'redirect_type' => 'integer',
            'hit_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
