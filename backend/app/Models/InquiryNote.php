<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit fix (High remediation) — a staff follow-up note on an Inquiry
 * (Database Design's documented inquiry_notes table, never implemented
 * before). No audit trait — `user_id` (the note's own author) already
 * serves that purpose for a table with no separate "editor", and notes
 * are create-only (no edit/delete endpoint) so `created_by`/`updated_by`
 * would be redundant.
 */
class InquiryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'user_id',
        'body',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
