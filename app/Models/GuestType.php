<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'key',
        'name_ar',
        'color',
        'icon',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];

    public function displayNameAr(): string
    {
        if ($this->key === 'normal' || $this->name_ar === 'عادي') {
            return 'عام';
        }

        return $this->name_ar;
    }

    public function getDisplayNameArAttribute(): string
    {
        return $this->displayNameAr();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }
}
