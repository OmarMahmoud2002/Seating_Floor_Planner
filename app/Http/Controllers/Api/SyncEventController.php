<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\EventSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncEventController extends Controller
{
    public function __invoke(Request $request, EventSyncService $events): JsonResponse
    {
        $data = $request->validate([
            'external_user_id' => ['required', 'integer', 'min:1'],
            'external_event_id' => ['required', 'integer', 'min:1'],
            'external_event_uuid' => ['nullable', 'uuid'],
            'name' => ['required_without:title', 'string', 'max:255'],
            'title' => ['required_without:name', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_status' => ['nullable', 'string', 'max:40'],
        ]);

        $event = $events->sync($data);

        return response()->json([
            'data' => [
                'event_id' => $event->id,
                'organization_id' => $event->organization_id,
                'external_event_id' => $event->external_event_id,
            ],
        ]);
    }
}
