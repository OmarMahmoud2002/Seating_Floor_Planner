<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SsoToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_hash',
        'user_id',
        'organization_id',
        'event_id',
        'target',
        'redirect_path',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'organization_id' => 'integer',
        'event_id' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
