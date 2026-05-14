<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\GuestSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncGuestGiftController extends Controller
{
    public function __invoke(Request $request, GuestSyncService $guests): JsonResponse
    {
        $data = $request->validate([
            'external_user_id' => ['required', 'integer', 'min:1'],
            'external_guest_id' => ['required', 'integer', 'min:1'],
            'gift_status' => ['required', 'string', 'in:not_used,used,Not Used,Used'],
            'gift_used_at' => ['nullable', 'date'],
        ]);

        $guest = $guests->updateGift($data);

        return response()->json([
            'data' => [
                'guest_id' => $guest->id,
                'gift_status' => $guest->gift_status,
                'gift_used_at' => optional($guest->gift_used_at)->toIso8601String(),
            ],
        ]);
    }
}
