<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ORGANIZATION_ADMIN = 'organization_admin';
    public const ROLE_STAFF = 'staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'role',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'organization_id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isOrganizationAdmin(): bool
    {
        return $this->role === self::ROLE_ORGANIZATION_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function canAccessOrganization(?int $organizationId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $organizationId !== null && (int) $this->organization_id === (int) $organizationId;
    }

    public function canAccessEvent(Event $event): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ((int) $event->user_id === (int) $this->id) {
            return true;
        }

        return $this->canAccessOrganization($event->organization_id);
    }

    public function canManageEvent(Event $event): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ((int) $event->user_id === (int) $this->id) {
            return true;
        }

        return $this->isOrganizationAdmin()
            && $this->canAccessOrganization($event->organization_id);
    }
}
