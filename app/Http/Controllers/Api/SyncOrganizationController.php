<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\OrganizationSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncOrganizationController extends Controller
{
    public function __invoke(Request $request, OrganizationSyncService $organizations): JsonResponse
    {
        $data = $request->validate([
            'external_user_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo_url' => ['nullable', 'url', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ]);

        $result = $organizations->sync($data);
        $organization = $result['organization'];
        $user = $result['user'];

        return response()->json([
            'data' => [
                'organization_id' => $organization->id,
                'external_user_id' => $organization->external_user_id,
                'user_id' => $user->id,
            ],
        ]);
    }
}
