<?php

namespace App\Services\Sso;

use App\Models\Event;
use App\Models\Organization;
use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SsoLinkService
{
    public const TARGET_ORGANIZATION_DASHBOARD = 'organization_dashboard';
    public const TARGET_EVENT_FLOORPLANS = 'event_floorplans';
    public const TARGET_ADMIN_DASHBOARD = 'admin_dashboard';

    /**
     * @return array<int, string>
     */
    public static function allowedTargets(): array
    {
        return [
            self::TARGET_ORGANIZATION_DASHBOARD,
            self::TARGET_EVENT_FLOORPLANS,
            self::TARGET_ADMIN_DASHBOARD,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{sso_token: SsoToken, plain_token: string, url: string}
     */
    public function createLink(array $data): array
    {
        $target = (string) $data['target'];
        $organization = null;
        $event = null;
        $user = null;
        $isSuperAdmin = ($data['role'] ?? null) === User::ROLE_SUPER_ADMIN;

        if ($target === self::TARGET_ADMIN_DASHBOARD || $isSuperAdmin) {
            $user = User::query()
                ->where('role', User::ROLE_SUPER_ADMIN)
                ->oldest()
                ->firstOrFail();
        } else {
            $organization = Organization::query()
                ->where('external_user_id', (int) $data['external_user_id'])
                ->firstOrFail();

            $user = $organization->users()
                ->where('role', User::ROLE_ORGANIZATION_ADMIN)
                ->oldest()
                ->firstOrFail();
        }

        if ($target === self::TARGET_EVENT_FLOORPLANS) {
            $eventQuery = Event::query()
                ->where('external_event_id', (int) $data['external_event_id']);

            if ($organization) {
                $eventQuery->where('organization_id', $organization->id);
            }

            $event = $eventQuery->firstOrFail();
            $organization = $organization ?: $event->organization;
        }

        $plainToken = Str::random(64);
        $ssoToken = SsoToken::query()->create([
            'token_hash' => $this->hashToken($plainToken),
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'event_id' => $event?->id,
            'target' => $target,
            'redirect_path' => $this->redirectPath($target, $event),
            'expires_at' => now()->addMinutes($this->ttlMinutes()),
            'metadata' => $data['metadata'] ?? null,
        ]);

        return [
            'sso_token' => $ssoToken,
            'plain_token' => $plainToken,
            'url' => route('sso.consume', $plainToken),
        ];
    }

    public function consume(string $plainToken): SsoToken
    {
        $ssoToken = SsoToken::query()
            ->with('user')
            ->where('token_hash', $this->hashToken($plainToken))
            ->first();

        if (! $ssoToken || ! $ssoToken->isUsable()) {
            throw new NotFoundHttpException();
        }

        $ssoToken->forceFill(['used_at' => now()])->save();

        return $ssoToken;
    }

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function redirectPath(string $target, ?Event $event): string
    {
        return match ($target) {
            self::TARGET_EVENT_FLOORPLANS => route('events.show', $event, false),
            self::TARGET_ADMIN_DASHBOARD,
            self::TARGET_ORGANIZATION_DASHBOARD => route('dashboard', [], false),
            default => throw new NotFoundHttpException(),
        };
    }

    private function ttlMinutes(): int
    {
        return max(1, (int) config('services.seating.sso_token_ttl', 5));
    }
}
