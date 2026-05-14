<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_organizations_index_and_show_pages(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $organization = Organization::factory()->create([
            'external_user_id' => 25,
            'name' => 'Perfection Events',
            'email' => 'ops@example.com',
        ]);
        $owner = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);
        $event = Event::factory()->for($owner)->create([
            'organization_id' => $organization->id,
            'name' => 'Linked Event',
        ]);
        Guest::factory()->for($event)->create([
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('organizations.index'))
            ->assertOk()
            ->assertSee('Perfection Events')
            ->assertSee('25')
            ->assertSee('1 حدث')
            ->assertSee('1 ضيف');

        $this->actingAs($superAdmin)
            ->get(route('organizations.show', $organization))
            ->assertOk()
            ->assertSee('Perfection Events')
            ->assertSee('Linked Event')
            ->assertSee($owner->email);
    }

    public function test_non_super_admin_cannot_view_organization_pages(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
        ]);

        $this->actingAs($user)
            ->get(route('organizations.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('organizations.show', $organization))
            ->assertForbidden();
    }

    public function test_super_admin_dashboard_shows_all_organizations_and_events(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $firstOrganization = Organization::factory()->create(['name' => 'First Org']);
        $secondOrganization = Organization::factory()->create(['name' => 'Second Org']);

        Event::factory()->create([
            'organization_id' => $firstOrganization->id,
            'name' => 'First Event',
        ]);
        Event::factory()->create([
            'organization_id' => $secondOrganization->id,
            'name' => 'Second Event',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('المنظمات')
            ->assertSee('إدارة المنظمات')
            ->assertSee('First Event')
            ->assertSee('Second Event');
    }
}
