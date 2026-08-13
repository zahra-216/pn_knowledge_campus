<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A public form submission (Contact page, Course Detail's "Enquire Now").
 * See the migration's docblock for why this was originally a minimal
 * slice — the admin inbox (status/search/export) shipped later, and
 * `assigned_to` + `notes` (audit fix, High remediation) complete the
 * staff-assignment/follow-up workflow the SRS and Database Design
 * document both specified from the start.
 */
class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'source_page',
        'course_id',
        'international_applicant',
        'status',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'international_applicant' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(InquiryNote::class)->latest();
    }
}
