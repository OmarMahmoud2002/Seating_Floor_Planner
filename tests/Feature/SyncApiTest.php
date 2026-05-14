<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\GuestType;
use App\Models\Organization;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.seating.sync_api_token' => 'sync-secret']);

        $this->seedGuestTypes();
    }

    public function test_sync_routes_reject_missing_or_invalid_bearer_token(): void
    {
        $payload = [
            'external_user_id' => 25,
            'name' => 'Perfection Events',
        ];

        $this->postJson(route('api.sync.organizations'), $payload)
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid sync API token.']);

        $this->withToken('wrong-token')
            ->postJson(route('api.sync.organizations'), $payload)
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid sync API token.']);
    }

    public function test_sync_api_creates_and_updates_organization(): void
    {
        $this->syncOrganization([
            'name' => 'Perfection Events',
            'email' => 'events@example.com',
            'phone' => '0500000000',
            'metadata' => ['source' => 'eventos_25'],
        ])
            ->assertOk()
            ->assertJsonPath('data.external_user_id', 25);

        $organization = Organization::query()->where('external_user_id', 25)->firstOrFail();

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'external_user_id' => 25,
            'name' => 'Perfection Events',
            'email' => 'events@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'email' => 'eventos-org-25@sync.local',
        ]);

        $this->syncOrganization([
            'name' => 'Perfection Events Updated',
            'email' => 'updated@example.com',
        ])->assertOk();

        $this->assertSame(1, Organization::query()->where('external_user_id', 25)->count());
        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Perfection Events Updated',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_sync_api_creates_and_updates_event(): void
    {
        $organization = $this->createSyncedOrganization();

        $this->syncEvent([
            'external_event_id' => 100,
            'external_event_uuid' => '11111111-1111-4111-8111-111111111111',
            'title' => 'Evento Main',
            'type' => 'conference',
            'event_date' => '2026-06-15',
            'location' => 'Main Hall',
            'description' => 'Synced event',
            'external_status' => 'Open',
        ])
            ->assertOk()
            ->assertJsonPath('data.external_event_id', 100);

        $event = Event::query()
            ->where('organization_id', $organization->id)
            ->where('external_event_id', 100)
            ->firstOrFail();

        $this->assertSame('Evento Main', $event->name);
        $this->assertSame($organization->id, $event->organization_id);
        $this->assertNotNull($event->last_synced_at);

        $this->syncEvent([
            'external_event_id' => 100,
            'name' => 'Evento Updated',
            'event_date' => '2026-06-16',
        ])->assertOk();

        $this->assertSame(1, Event::query()->where('organization_id', $organization->id)->where('external_event_id', 100)->count());
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Evento Updated',
        ]);
    }

    public function test_sync_api_creates_normal_vip_vvip_and_media_guests(): void
    {
        $event = $this->createSyncedEvent();

        $this->syncGuest([
            'external_guest_id' => 501,
            'guest_type_key' => 'normal',
            'name' => 'Normal Guest',
            'phone' => '01111111111',
            'status' => 'Submit',
            'gift_status' => 'Not Used',
        ])
            ->assertOk()
            ->assertJsonPath('data.external_guest_id', 501);

        $this->syncGuest([
            'external_guest_id' => 502,
            'guest_type_key' => 'vip',
            'name' => 'VIP Guest',
            'email' => 'vip@example.com',
            'external_payload' => ['company' => 'Perfection'],
        ])
            ->assertOk()
            ->assertJsonPath('data.external_guest_id', 502);

        $this->syncGuest([
            'external_guest_id' => 503,
            'guest_type_key' => 'vvip',
            'name' => 'VVIP Guest',
            'email' => 'vvip@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('data.external_guest_id', 503);

        $this->syncGuest([
            'external_guest_id' => 504,
            'guest_type_key' => 'media',
            'name' => 'Media Guest',
            'email' => 'media@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('data.external_guest_id', 504);

        $normalGuest = Guest::query()->where('external_guest_id', 501)->firstOrFail();
        $vipGuest = Guest::query()->where('external_guest_id', 502)->firstOrFail();
        $vvipGuest = Guest::query()->where('external_guest_id', 503)->firstOrFail();
        $mediaGuest = Guest::query()->where('external_guest_id', 504)->firstOrFail();

        $this->assertSame($event->id, $normalGuest->event_id);
        $this->assertSame('normal', $normalGuest->guestType->key);
        $this->assertSame('registered', $normalGuest->status);
        $this->assertSame('not_used', $normalGuest->gift_status);
        $this->assertSame('vip', $vipGuest->guestType->key);
        $this->assertSame('VIP Guest', $vipGuest->name);
        $this->assertSame('vvip', $vvipGuest->guestType->key);
        $this->assertSame('VVIP Guest', $vvipGuest->name);
        $this->assertSame('media', $mediaGuest->guestType->key);
        $this->assertSame('Media Guest', $mediaGuest->name);
    }

    public function test_sync_api_updates_guest_attendance(): void
    {
        $guest = $this->createSyncedGuest();

        $this->withToken('sync-secret')
            ->patchJson(route('api.sync.guests.status'), [
                'external_user_id' => 25,
                'external_guest_id' => $guest->external_guest_id,
                'status' => 'Attended',
                'checked_in_at' => '2026-06-15 10:30:00',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'attended');

        $guest->refresh();

        $this->assertSame('attended', $guest->status);
        $this->assertSame('2026-06-15 10:30:00', $guest->checked_in_at->format('Y-m-d H:i:s'));
    }

    public function test_sync_api_updates_guest_gift_status(): void
    {
        $guest = $this->createSyncedGuest();

        $this->withToken('sync-secret')
            ->patchJson(route('api.sync.guests.gift'), [
                'external_user_id' => 25,
                'external_guest_id' => $guest->external_guest_id,
                'gift_status' => 'Used',
                'gift_used_at' => '2026-06-15 11:00:00',
            ])
            ->assertOk()
            ->assertJsonPath('data.gift_status', 'used');

        $guest->refresh();

        $this->assertSame('used', $guest->gift_status);
        $this->assertSame('2026-06-15 11:00:00', $guest->gift_used_at->format('Y-m-d H:i:s'));
    }

    public function test_sync_api_deletes_guest_and_unassigns_seat(): void
    {
        $guest = $this->createSyncedGuest();
        $floorplan = Floorplan::factory()->for($guest->event)->create();
        $seat = Seat::query()->create([
            'floorplan_id' => $floorplan->id,
            'guest_id' => $guest->id,
            'table_key' => 'table-1',
            'table_name' => 'طاولة 1',
            'seat_key' => 'table-1-seat-1',
            'seat_number' => 1,
            'x' => 100,
            'y' => 100,
            'rotation' => 0,
            'status' => 'occupied',
        ]);

        $this->withToken('sync-secret')
            ->deleteJson(route('api.sync.guests.delete'), [
                'external_user_id' => 25,
                'external_guest_id' => $guest->external_guest_id,
            ])
            ->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
        $this->assertDatabaseHas('seats', [
            'id' => $seat->id,
            'guest_id' => null,
            'status' => 'available',
        ]);
    }

    private function createSyncedOrganization(): Organization
    {
        $this->syncOrganization()->assertOk();

        return Organization::query()->where('external_user_id', 25)->firstOrFail();
    }

    private function createSyncedEvent(): Event
    {
        $this->createSyncedOrganization();
        $this->syncEvent()->assertOk();

        return Event::query()
            ->whereHas('organization', fn ($query) => $query->where('external_user_id', 25))
            ->where('external_event_id', 100)
            ->firstOrFail();
    }

    private function createSyncedGuest(): Guest
    {
        $this->createSyncedEvent();
        $this->syncGuest()->assertOk();

        return Guest::query()
            ->whereHas('organization', fn ($query) => $query->where('external_user_id', 25))
            ->where('external_guest_id', 500)
            ->firstOrFail();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function syncOrganization(array $overrides = [])
    {
        return $this->withToken('sync-secret')
            ->postJson(route('api.sync.organizations'), $overrides + [
                'external_user_id' => 25,
                'name' => 'Perfection Events',
                'email' => 'events@example.com',
            ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function syncEvent(array $overrides = [])
    {
        return $this->withToken('sync-secret')
            ->postJson(route('api.sync.events'), $overrides + [
                'external_user_id' => 25,
                'external_event_id' => 100,
                'title' => 'Evento Main',
                'event_date' => '2026-06-15',
            ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function syncGuest(array $overrides = [])
    {
        return $this->withToken('sync-secret')
            ->postJson(route('api.sync.guests'), $overrides + [
                'external_user_id' => 25,
                'external_event_id' => 100,
                'external_guest_id' => 500,
                'guest_type_key' => 'vip',
                'name' => 'VIP Guest',
            ]);
    }

    private function seedGuestTypes(): void
    {
        GuestType::query()->create([
            'key' => 'normal',
            'name_ar' => 'عادي',
            'color' => '#A19F9E',
            'icon' => 'user',
            'sort_order' => 10,
            'is_default' => true,
        ]);
        GuestType::query()->create([
            'key' => 'vip',
            'name_ar' => 'VIP',
            'color' => '#E7C539',
            'icon' => 'star',
            'sort_order' => 20,
            'is_default' => true,
        ]);
        GuestType::query()->create([
            'key' => 'vvip',
            'name_ar' => 'VVIP',
            'color' => '#7C3AED',
            'icon' => 'gem',
            'sort_order' => 30,
            'is_default' => true,
        ]);
        GuestType::query()->create([
            'key' => 'media',
            'name_ar' => 'media',
            'color' => '#0284C7',
            'icon' => 'camera',
            'sort_order' => 40,
            'is_default' => true,
        ]);
    }
}
