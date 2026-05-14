<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_user_id',
        'name',
        'email',
        'phone',
        'logo_url',
        'metadata',
    ];

    protected $casts = [
        'external_user_id' => 'integer',
        'metadata' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function guestTypes(): HasMany
    {
        return $this->hasMany(GuestType::class);
    }
}
