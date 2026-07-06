<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A public form submission (Contact page, Course Detail's "Enquire Now").
 * See the migration's docblock for why this is a minimal slice — no
 * admin management screen yet, just the capture write-path and a status
 * column for whichever future milestone builds the inbox.
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
}
