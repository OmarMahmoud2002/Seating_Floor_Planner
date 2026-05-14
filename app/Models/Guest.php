<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'external_guest_id',
        'event_id',
        'guest_type_id',
        'name',
        'phone',
        'email',
        'notes',
        'status',
        'gift_status',
        'checked_in_at',
        'gift_used_at',
        'external_payload',
        'last_synced_at',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'external_guest_id' => 'integer',
        'checked_in_at' => 'datetime',
        'gift_used_at' => 'datetime',
        'external_payload' => 'array',
        'last_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest): void {
            if (! $guest->organization_id && $guest->event_id) {
                $guest->organization_id = Event::query()
                    ->whereKey($guest->event_id)
                    ->value('organization_id');
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function guestType(): BelongsTo
    {
        return $this->belongsTo(GuestType::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function scopeForEvent(Builder $query, Event $event): Builder
    {
        return $query->where('event_id', $event->id);
    }
}
