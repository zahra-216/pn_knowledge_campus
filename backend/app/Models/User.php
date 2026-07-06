<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * CMS staff accounts. Phase 1 has no public/student-facing accounts — every
 * row here is an internal user assigned exactly one role via Spatie Laravel
 * Permission (Super Admin, Administrator, Content Editor, Marketing,
 * Admissions — see the SRS Permission Matrix).
 *
 * Database Design reference: Section 4.1 — Identity & Access.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasAuditColumns, HasFactory, HasRoles, Notifiable;

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
}
