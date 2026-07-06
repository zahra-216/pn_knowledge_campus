<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Milestone 20 (Online Applications) — see the migration's docblock for
 * the visitor-identity design (application_number + email, no real
 * auth). Documents attach via a multi-file 'documents' media
 * collection, each tagged with a custom_property 'label' (e.g.
 * "Transcript", "National ID") — same pattern as GalleryAlbum's
 * per-item 'caption'.
 *
 * Audit fix (Critical remediation) — 'documents' deliberately uses the
 * 'local' disk (storage/app/private, no public symlink, no URL config),
 * unlike every other media collection in this app. These files can
 * contain applicant PII (passports, transcripts, national IDs); every
 * other collection is fine on the public disk because it's marketing
 * content meant to be world-readable. There is no direct getUrl() for
 * this collection — ApplicationDocumentResource instead points at
 * ApplicationController::downloadDocument(), which re-checks the same
 * ownership/staff-permission gate every other mutating action here
 * already enforces before streaming the file.
 */
class Application extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'application_number',
        'course_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'nationality',
        'address',
        'international_applicant',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'international_applicant' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Application $application) {
            if (! $application->application_number) {
                $application->application_number = self::generateApplicationNumber();
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')->useDisk('local');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /** Case-insensitive — visitors shouldn't be locked out over capitalization. */
    public function ownedBy(string $email): bool
    {
        return strcasecmp($this->email, trim($email)) === 0;
    }

    private static function generateApplicationNumber(): string
    {
        do {
            $candidate = 'PNKC-'.now()->year.'-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('application_number', $candidate)->exists());

        return $candidate;
    }
}
