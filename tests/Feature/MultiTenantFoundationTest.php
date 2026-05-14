<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_events_and_guests_inherit_the_users_organization(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);

        $event = $user->events()->create([
            'name' => 'Local Seating Event',
            'event_date' => '2026-06-15',
        ]);

        $guest = $event->guests()->create([
            'name' => 'Local Guest',
        ]);

        $this->assertSame($organization->id, $event->organization_id);
        $this->assertSame($organization->id, $guest->organization_id);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'organization_id' => $organization->id,
            'external_event_id' => null,
        ]);
        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'organization_id' => $organization->id,
            'external_guest_id' => null,
            'status' => 'registered',
            'gift_status' => 'not_used',
        ]);
    }

    public function test_user_role_helpers_respect_organization_access(): void
    {
        $organization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);
        $superAdmin = User::factory()->create([
            'organization_id' => null,
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->assertTrue($admin->isOrganizationAdmin());
        $this->assertTrue($admin->canAccessOrganization($organization->id));
        $this->assertFalse($admin->canAccessOrganization($otherOrganization->id));
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->canAccessOrganization($otherOrganization->id));
    }

    public function test_organization_admin_can_view_and_manage_events_in_the_same_organization(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);
        $owner = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_STAFF,
        ]);
        $event = Event::factory()->for($owner)->create([
            'organization_id' => $organization->id,
            'name' => 'Organization Event',
        ]);
        $floorplan = Floorplan::factory()->for($event)->create();
        $guest = Guest::factory()->for($event)->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($admin)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Organization Event');

        $this->assertTrue($admin->can('view', $event));
        $this->assertTrue($admin->can('update', $event));
        $this->assertTrue($admin->can('update', $floorplan));
        $this->assertTrue($admin->can('update', $guest));
    }

    public function test_super_admin_visible_queries_include_all_organizations(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $firstOrganization = Organization::factory()->create();
        $secondOrganization = Organization::factory()->create();

        Event::factory()->create([
            'organization_id' => $firstOrganization->id,
            'name' => 'First Organization Event',
        ]);
        Event::factory()->create([
            'organization_id' => $secondOrganization->id,
            'name' => 'Second Organization Event',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('First Organization Event')
            ->assertSee('Second Organization Event');

        $this->assertSame(2, Event::query()->visibleTo($superAdmin)->count());
    }
}
