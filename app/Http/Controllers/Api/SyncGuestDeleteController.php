<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\GuestSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncGuestDeleteController extends Controller
{
    public function __invoke(Request $request, GuestSyncService $guests): JsonResponse
    {
        $data = $request->validate([
            'external_user_id' => ['required', 'integer', 'min:1'],
            'external_guest_id' => ['required', 'integer', 'min:1'],
        ]);

        $guests->delete($data);

        return response()->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }
}
