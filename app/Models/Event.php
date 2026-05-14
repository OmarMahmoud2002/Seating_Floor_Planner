<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'external_event_id',
        'external_event_uuid',
        'external_status',
        'last_synced_at',
        'name',
        'type',
        'event_date',
        'location',
        'description',
        'preview_token',
        'preview_enabled',
        'vip_registration_enabled',
        'vvip_registration_enabled',
        'media_registration_enabled',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'external_event_id' => 'integer',
        'event_date' => 'date',
        'last_synced_at' => 'datetime',
        'preview_enabled' => 'boolean',
        'vip_registration_enabled' => 'boolean',
        'vvip_registration_enabled' => 'boolean',
        'media_registration_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (! $event->preview_token) {
                $event->preview_token = static::newPreviewToken();
            }

            if (! $event->organization_id && $event->user_id) {
                $event->organization_id = User::query()
                    ->whereKey($event->user_id)
                    ->value('organization_id');
            }
        });
    }

    public static function newPreviewToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::query()->where('preview_token', $token)->exists());

        return $token;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function floorplans(): HasMany
    {
        return $this->hasMany(Floorplan::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id);

            if ($user->organization_id) {
                $query->orWhere('organization_id', $user->organization_id);
            }
        });
    }
}
