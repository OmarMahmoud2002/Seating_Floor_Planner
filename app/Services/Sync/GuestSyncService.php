<?php

namespace App\Services\Sync;

use App\Models\Guest;

class GuestSyncService
{
    public function __construct(
        private readonly EventSyncService $events,
        private readonly GuestTypeMapper $guestTypes,
        private readonly ExternalStatusMapper $statuses,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sync(array $data): Guest
    {
        $event = $this->events->eventFor(
            (int) $data['external_user_id'],
            (int) $data['external_event_id']
        );
        $organization = $event->organization;
        $guestType = $this->guestTypes->fromKey($data['guest_type_key']);

        return Guest::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'external_guest_id' => (int) $data['external_guest_id'],
            ],
            [
                'event_id' => $event->id,
                'guest_type_id' => $guestType->id,
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $this->statuses->guestStatus($data['status'] ?? null),
                'gift_status' => $this->statuses->giftStatus($data['gift_status'] ?? null),
                'external_payload' => $data['external_payload'] ?? null,
                'last_synced_at' => now(),
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateStatus(array $data): Guest
    {
        $guest = $this->guestFor(
            (int) $data['external_user_id'],
            (int) $data['external_guest_id']
        );
        $status = $this->statuses->guestStatus($data['status']);

        $guest->forceFill([
            'status' => $status,
            'checked_in_at' => $data['checked_in_at'] ?? ($status === 'attended' ? now() : null),
            'last_synced_at' => now(),
        ])->save();

        return $guest;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateGift(array $data): Guest
    {
        $guest = $this->guestFor(
            (int) $data['external_user_id'],
            (int) $data['external_guest_id']
        );
        $giftStatus = $this->statuses->giftStatus($data['gift_status']);

        $guest->forceFill([
            'gift_status' => $giftStatus,
            'gift_used_at' => $data['gift_used_at'] ?? ($giftStatus === 'used' ? now() : null),
            'last_synced_at' => now(),
        ])->save();

        return $guest;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function delete(array $data): void
    {
        $guest = $this->findGuest(
            (int) $data['external_user_id'],
            (int) $data['external_guest_id']
        );

        if (! $guest) {
            return;
        }

        $guest->seats()->update([
            'guest_id' => null,
            'status' => 'available',
        ]);
        $guest->delete();
    }

    public function guestFor(int $externalUserId, int $externalGuestId): Guest
    {
        return $this->baseGuestQuery($externalUserId, $externalGuestId)->firstOrFail();
    }

    public function findGuest(int $externalUserId, int $externalGuestId): ?Guest
    {
        return $this->baseGuestQuery($externalUserId, $externalGuestId)->first();
    }

    private function baseGuestQuery(int $externalUserId, int $externalGuestId)
    {
        $organization = $this->events->organizationFor($externalUserId);

        return Guest::query()
            ->where('organization_id', $organization->id)
            ->where('external_guest_id', $externalGuestId);
    }
}
