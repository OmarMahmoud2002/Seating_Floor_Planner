<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floorplan extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'width',
        'height',
        'unit',
        'paper_size',
        'orientation',
        'grid_size',
        'background_image_path',
        'design_json',
        'last_saved_at',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'grid_size' => 'integer',
        'design_json' => 'array',
        'last_saved_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function backgroundImageUrl(): ?string
    {
        if (! $this->background_image_path) {
            return null;
        }

        return '/storage/'.ltrim($this->background_image_path, '/');
    }
}
