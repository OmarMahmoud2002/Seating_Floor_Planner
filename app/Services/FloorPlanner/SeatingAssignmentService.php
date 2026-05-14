<?php

namespace App\Services\FloorPlanner;

use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeatingAssignmentService
{
    public function assign(Floorplan $floorplan, int $guestId, string $seatKey): Seat
    {
        return DB::transaction(function () use ($floorplan, $guestId, $seatKey): Seat {
            $guest = Guest::query()->findOrFail($guestId);

            if ($guest->event_id !== $floorplan->event_id) {
                throw ValidationException::withMessages([
                    'guest_id' => 'هذا الضيف لا ينتمي لنفس الحدث.',
                ]);
            }

            $seat = Seat::query()
                ->where('floorplan_id', $floorplan->id)
                ->where('seat_key', $seatKey)
                ->lockForUpdate()
                ->firstOrFail();

            if ($seat->guest_id && $seat->guest_id !== $guest->id) {
                throw ValidationException::withMessages([
                    'seat_key' => 'هذا المقعد محجوز بالفعل.',
                ]);
            }

            Seat::query()
                ->where('floorplan_id', $floorplan->id)
                ->where('guest_id', $guest->id)
                ->where('id', '!=', $seat->id)
                ->update([
                    'guest_id' => null,
                    'status' => 'available',
                ]);

            $seat->forceFill([
                'guest_id' => $guest->id,
                'status' => 'occupied',
            ])->save();

            return $seat->fresh(['guest.guestType']);
        });
    }

    public function unassign(Floorplan $floorplan, string $seatKey): Seat
    {
        return DB::transaction(function () use ($floorplan, $seatKey): Seat {
            $seat = Seat::query()
                ->where('floorplan_id', $floorplan->id)
                ->where('seat_key', $seatKey)
                ->lockForUpdate()
                ->firstOrFail();

            $seat->forceFill([
                'guest_id' => null,
                'status' => 'available',
            ])->save();

            return $seat->fresh();
        });
    }
}
