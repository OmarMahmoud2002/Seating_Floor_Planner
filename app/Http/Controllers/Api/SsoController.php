<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sso\SsoLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SsoController extends Controller
{
    public function __invoke(Request $request, SsoLinkService $ssoLinks): JsonResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', Rule::in(SsoLinkService::allowedTargets())],
            'external_user_id' => [
                Rule::requiredIf(fn () => $request->input('target') !== SsoLinkService::TARGET_ADMIN_DASHBOARD),
                'integer',
                'min:1',
            ],
            'external_event_id' => [
                Rule::requiredIf(fn () => $request->input('target') === SsoLinkService::TARGET_EVENT_FLOORPLANS),
                'integer',
                'min:1',
            ],
            'metadata' => ['nullable', 'array'],
        ]);

        $link = $ssoLinks->createLink($data);
        $ssoToken = $link['sso_token'];

        return response()->json([
            'data' => [
                'url' => $link['url'],
                'target' => $ssoToken->target,
                'expires_at' => $ssoToken->expires_at->toIso8601String(),
            ],
        ]);
    }
}
