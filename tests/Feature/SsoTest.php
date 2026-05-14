<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\SsoToken;
use App\Models\User;
use App\Services\Sso\SsoLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.seating.sync_api_token' => 'sync-secret',
            'services.seating.sso_token_ttl' => 5,
        ]);
    }

    public function test_create_sso_link_is_protected_by_sync_api_token(): void
    {
        $this->postJson(route('api.sync.sso-links'), [
            'target' => SsoLinkService::TARGET_ORGANIZATION_DASHBOARD,
            'external_user_id' => 25,
        ])->assertUnauthorized();

        $this->withToken('wrong-token')
            ->postJson(route('api.sync.sso-links'), [
                'target' => SsoLinkService::TARGET_ORGANIZATION_DASHBOARD,
                'external_user_id' => 25,
            ])
            ->assertUnauthorized();
    }

    public function test_valid_sso_token_logs_user_in_and_redirects_once(): void
    {
        $organization = $this->createOrganizationWithAdmin();

        $response = $this->withToken('sync-secret')
            ->postJson(route('api.sync.sso-links'), [
                'target' => SsoLinkService::TARGET_ORGANIZATION_DASHBOARD,
                'external_user_id' => $organization->external_user_id,
            ])
            ->assertOk()
            ->assertJsonPath('data.target', SsoLinkService::TARGET_ORGANIZATION_DASHBOARD);

        $url = $response->json('data.url');
        $plainToken = basename((string) parse_url($url, PHP_URL_PATH));

        $this->assertDatabaseHas('sso_tokens', [
            'token_hash' => hash('sha256', $plainToken),
            'organization_id' => $organization->id,
            'target' => SsoLinkService::TARGET_ORGANIZATION_DASHBOARD,
            'redirect_path' => route('dashboard', [], false),
            'used_at' => null,
        ]);

        $this->get($url)
            ->assertRedirect(route('dashboard', [], false));

        $this->assertAuthenticatedAs($organization->users()->first());
        $this->assertNotNull(SsoToken::query()->firstOrFail()->used_at);

        $this->get($url)->assertNotFound();
    }

    public function test_event_floorplans_sso_target_redirects_to_event_details(): void
    {
        $organization = $this->createOrganizationWithAdmin();
        $owner = $organization->users()->firstOrFail();
        $event = Event::factory()->for($owner)->create([
            'organization_id' => $organization->id,
            'external_event_id' => 100,
            'name' => 'Synced Event',
        ]);

        $response = $this->withToken('sync-secret')
            ->postJson(route('api.sync.sso-links'), [
                'target' => SsoLinkService::TARGET_EVENT_FLOORPLANS,
                'external_user_id' => $organization->external_user_id,
                'external_event_id' => $event->external_event_id,
            ])
            ->assertOk()
            ->assertJsonPath('data.target', SsoLinkService::TARGET_EVENT_FLOORPLANS);

        $this->get($response->json('data.url'))
            ->assertRedirect(route('events.show', $event, false));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_admin_dashboard_sso_target_logs_in_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->withToken('sync-secret')
            ->postJson(route('api.sync.sso-links'), [
                'target' => SsoLinkService::TARGET_ADMIN_DASHBOARD,
            ])
            ->assertOk()
            ->assertJsonPath('data.target', SsoLinkService::TARGET_ADMIN_DASHBOARD);

        $this->get($response->json('data.url'))
            ->assertRedirect(route('dashboard', [], false));

        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_expired_sso_token_is_rejected(): void
    {
        $organization = $this->createOrganizationWithAdmin();
        $link = app(SsoLinkService::class)->createLink([
            'target' => SsoLinkService::TARGET_ORGANIZATION_DASHBOARD,
            'external_user_id' => $organization->external_user_id,
        ]);

        $link['sso_token']->forceFill([
            'expires_at' => now()->subMinute(),
        ])->save();

        $this->get(route('sso.consume', $link['plain_token']))
            ->assertNotFound();

        $this->assertGuest();
        $this->assertNull($link['sso_token']->refresh()->used_at);
    }

    public function test_used_sso_token_is_rejected(): void
    {
        $organization = $this->createOrganizationWithAdmin();
        $link = app(SsoLinkService::class)->createLink([
            'target' => SsoLinkService::TARGET_ORGANIZATION_DASHBOARD,
            'external_user_id' => $organization->external_user_id,
        ]);

        $link['sso_token']->forceFill([
            'used_at' => now(),
        ])->save();

        $this->get(route('sso.consume', $link['plain_token']))
            ->assertNotFound();

        $this->assertGuest();
    }

    public function test_create_sso_link_validates_target_allowlist(): void
    {
        $this->withToken('sync-secret')
            ->postJson(route('api.sync.sso-links'), [
                'target' => 'https://evil.example/redirect',
                'external_user_id' => 25,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target');
    }

    private function createOrganizationWithAdmin(): Organization
    {
        $organization = Organization::factory()->create([
            'external_user_id' => 25,
            'name' => 'Perfection Events',
        ]);

        User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'name' => 'Organization Admin',
            'email' => 'eventos-org-25@sync.local',
        ]);

        return $organization;
    }
}
