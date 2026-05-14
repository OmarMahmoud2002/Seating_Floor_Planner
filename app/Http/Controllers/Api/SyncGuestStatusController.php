<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\GuestSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncGuestStatusController extends Controller
{
    public function __invoke(Request $request, GuestSyncService $guests): JsonResponse
    {
        $data = $request->validate([
            'external_user_id' => ['required', 'integer', 'min:1'],
            'external_guest_id' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:registered,attended,Submit,Attended'],
            'checked_in_at' => ['nullable', 'date'],
        ]);

        $guest = $guests->updateStatus($data);

        return response()->json([
            'data' => [
                'guest_id' => $guest->id,
                'status' => $guest->status,
                'checked_in_at' => optional($guest->checked_in_at)->toIso8601String(),
            ],
        ]);
    }
}
