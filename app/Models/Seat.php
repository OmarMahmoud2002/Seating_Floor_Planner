<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'floorplan_id',
        'guest_id',
        'table_key',
        'table_name',
        'seat_key',
        'seat_number',
        'x',
        'y',
        'rotation',
        'status',
        'metadata',
    ];

    protected $casts = [
        'seat_number' => 'integer',
        'x' => 'decimal:2',
        'y' => 'decimal:2',
        'rotation' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function floorplan(): BelongsTo
    {
        return $this->belongsTo(Floorplan::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
