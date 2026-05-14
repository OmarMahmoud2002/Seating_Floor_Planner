<?php

namespace App\Services\Sync;

use App\Models\Event;
use App\Models\Organization;

class EventSyncService
{
    public function __construct(
        private readonly OrganizationSyncService $organizations,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sync(array $data): Event
    {
        $organization = $this->organizationFor((int) $data['external_user_id']);
        $owner = $this->organizations->syncOwnerFor($organization);

        return Event::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'external_event_id' => (int) $data['external_event_id'],
            ],
            [
                'user_id' => $owner->id,
                'external_event_uuid' => $data['external_event_uuid'] ?? null,
                'external_status' => $data['external_status'] ?? null,
                'last_synced_at' => now(),
                'name' => $data['name'] ?? $data['title'],
                'type' => $data['type'] ?? null,
                'event_date' => $data['event_date'] ?? null,
                'location' => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
            ]
        );
    }

    public function eventFor(int $externalUserId, int $externalEventId): Event
    {
        $organization = $this->organizationFor($externalUserId);

        return Event::query()
            ->where('organization_id', $organization->id)
            ->where('external_event_id', $externalEventId)
            ->firstOrFail();
    }

    public function organizationFor(int $externalUserId): Organization
    {
        return Organization::query()
            ->where('external_user_id', $externalUserId)
            ->firstOrFail();
    }
}
