<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\GuestSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncGuestController extends Controller
{
    public function __invoke(Request $request, GuestSyncService $guests): JsonResponse
    {
        $data = $request->validate([
            'external_user_id' => ['required', 'integer', 'min:1'],
            'external_event_id' => ['required', 'integer', 'min:1'],
            'external_guest_id' => ['required', 'integer', 'min:1'],
            'guest_type_key' => ['required', 'string', 'in:normal,vip,vvip,media'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:32'],
            'gift_status' => ['nullable', 'string', 'max:32'],
            'external_payload' => ['nullable', 'array'],
        ]);

        $guest = $guests->sync($data);

        return response()->json([
            'data' => [
                'guest_id' => $guest->id,
                'event_id' => $guest->event_id,
                'organization_id' => $guest->organization_id,
                'external_guest_id' => $guest->external_guest_id,
            ],
        ]);
    }
}
