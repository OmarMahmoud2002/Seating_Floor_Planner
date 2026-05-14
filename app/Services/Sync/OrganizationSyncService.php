<?php

namespace App\Services\Sync;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationSyncService
{
    /**
     * @param array<string, mixed> $data
     * @return array{organization: Organization, user: User}
     */
    public function sync(array $data): array
    {
        $organization = Organization::query()->updateOrCreate(
            ['external_user_id' => (int) $data['external_user_id']],
            [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'logo_url' => $data['logo_url'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]
        );

        $user = User::query()->firstOrCreate(
            ['email' => $this->syncUserEmail((int) $data['external_user_id'])],
            [
                'organization_id' => $organization->id,
                'role' => User::ROLE_ORGANIZATION_ADMIN,
                'name' => $data['name'],
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]
        );

        $user->forceFill([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'name' => $data['name'],
        ])->save();

        return [
            'organization' => $organization,
            'user' => $user,
        ];
    }

    public function syncOwnerFor(Organization $organization): User
    {
        $user = $organization->users()
            ->where('role', User::ROLE_ORGANIZATION_ADMIN)
            ->oldest()
            ->first();

        if ($user) {
            return $user;
        }

        return User::query()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'name' => $organization->name,
            'email' => $this->syncUserEmail((int) $organization->external_user_id),
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ]);
    }

    private function syncUserEmail(int $externalUserId): string
    {
        return "eventos-org-{$externalUserId}@sync.local";
    }
}
