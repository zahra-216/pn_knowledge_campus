<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * CMS staff accounts. Phase 1 has no public/student-facing accounts — every
 * row here is an internal user assigned exactly one role via Spatie Laravel
 * Permission (Super Admin, Administrator, Content Editor, Marketing,
 * Admissions — see the SRS Permission Matrix).
 *
 * Database Design reference: Section 4.1 — Identity & Access.
 *
 * HasMedia/'avatar' collection (audit fix, Medium remediation) — the
 * documented profile-picture capability staff had no way to use; this
 * closes the model-level gap. No upload UI exists yet in the Users
 * admin screen — deliberately out of scope here, since building that
 * is real net-new surface, not a relation fix.
 */
class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasAuditColumns, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * A permission-key list suitable for the /auth/me response, matching
     * the shape documented in the API Design (Section 9.1).
     */
    public function permissionKeys(): array
    {
        return $this->getAllPermissions()->pluck('name')->values()->all();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }
}
