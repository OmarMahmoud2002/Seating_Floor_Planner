<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\GuestType;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_event(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'name' => 'حفل افتتاح القاعة',
            'type' => 'حفل',
            'event_date' => '2026-06-15',
            'location' => 'قاعة بيرفكشن',
            'description' => 'حدث تجريبي',
            'vip_registration_enabled' => '1',
            'vvip_registration_enabled' => '1',
        ]);

        $event = Event::query()->first();

        $response->assertRedirect(route('events.floorplans.create', $event));
        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'name' => 'حفل افتتاح القاعة',
            'preview_enabled' => true,
            'vip_registration_enabled' => true,
            'vvip_registration_enabled' => true,
            'media_registration_enabled' => false,
        ]);
        $this->assertNotEmpty($event->preview_token);
    }

    public function test_create_event_page_shows_setup_steps(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('events.create'))
            ->assertOk()
            ->assertSee('بيانات الحدث')
            ->assertSee('إعداد المخطط')
            ->assertSee('محرر المخطط')
            ->assertSee('خيارات التسجيل')
            ->assertSee('تفعيل رابط VIP')
            ->assertSee('تفعيل رابط VVIP')
            ->assertSee('تفعيل رابط media')
            ->assertSee('VVIP')
            ->assertSee('media')
            ->assertSee('التالي: إعداد المخطط');
    }

    public function test_event_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_event_and_disable_preview(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('events.update', $event), [
            'name' => 'الاسم الجديد',
            'type' => 'مؤتمر',
            'event_date' => '2026-07-20',
            'location' => 'قاعة رئيسية',
            'description' => 'وصف محدث',
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'الاسم الجديد',
            'preview_enabled' => false,
        ]);
    }

    public function test_admin_cannot_view_another_users_event(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertForbidden();
    }

    public function test_preview_link_shows_read_only_event_when_enabled(): void
    {
        $event = Event::factory()->create([
            'name' => 'معرض العملاء',
            'preview_enabled' => true,
        ]);
        Floorplan::factory()->for($event)->create([
            'name' => 'مخطط المعاينة',
            'design_json' => [
                'version' => 1,
                'elements' => [
                    [
                        'id' => 'table-1',
                        'type' => 'table',
                        'label' => 'طاولة 1',
                        'x' => 80,
                        'y' => 70,
                        'width' => 120,
                        'height' => 90,
                        'tableShape' => 'round',
                        'seats' => [],
                    ],
                ],
            ],
        ]);

        $this->get(route('events.preview', $event->preview_token))
            ->assertOk()
            ->assertSee('معرض العملاء')
            ->assertDontSee('مخطط المعاينة')
            ->assertSee('هذه صفحة معاينة فقط')
            ->assertDontSee('حذف الحدث');
    }

    public function test_event_details_show_type_links_for_vip_vvip_and_media(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create([
            'preview_enabled' => true,
            'vip_registration_enabled' => true,
            'vvip_registration_enabled' => true,
            'media_registration_enabled' => true,
        ]);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('نسخ رابط VIP')
            ->assertSee('نسخ رابط VVIP')
            ->assertSee('نسخ رابط media')
            ->assertSee('guest_type_key=vip', false)
            ->assertSee('guest_type_key=vvip', false)
            ->assertSee('guest_type_key=media', false);
    }

    public function test_events_index_has_copy_buttons_for_type_links(): void
    {
        $user = User::factory()->create();
        Event::factory()->for($user)->create([
            'name' => 'فعالية بروابط',
            'preview_enabled' => true,
            'vip_registration_enabled' => true,
            'vvip_registration_enabled' => true,
            'media_registration_enabled' => true,
        ]);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('روابط التسجيل')
            ->assertSee('VIP')
            ->assertSee('VVIP')
            ->assertSee('media')
            ->assertSee('guest_type_key=vip', false)
            ->assertSee('guest_type_key=vvip', false)
            ->assertSee('guest_type_key=media', false);
    }

    public function test_floorplan_preview_link_shows_full_read_only_plan(): void
    {
        $event = Event::factory()->create([
            'name' => 'معرض العملاء',
            'preview_enabled' => true,
        ]);
        $floorplan = Floorplan::factory()->for($event)->create([
            'name' => 'مخطط المعاينة',
            'design_json' => [
                'version' => 1,
                'elements' => [
                    [
                        'id' => 'table-1',
                        'type' => 'table',
                        'label' => 'طاولة 1',
                        'x' => 80,
                        'y' => 70,
                        'width' => 120,
                        'height' => 90,
                        'tableShape' => 'round',
                        'seats' => [
                            ['key' => 'table-1-seat-1', 'number' => 1, 'label' => '1', 'x' => 60, 'y' => -28],
                        ],
                    ],
                ],
            ],
        ]);
        $guestType = GuestType::factory()->create(['name_ar' => 'VIP', 'color' => '#E7C539']);
        $guest = Guest::factory()->for($event)->for($guestType)->create(['name' => 'ضيف المعاينة']);

        Seat::query()->create([
            'floorplan_id' => $floorplan->id,
            'guest_id' => $guest->id,
            'table_key' => 'table-1',
            'table_name' => 'طاولة 1',
            'seat_key' => 'table-1-seat-1',
            'seat_number' => 1,
            'x' => 140,
            'y' => 42,
            'rotation' => 0,
            'status' => 'occupied',
        ]);

        $this->get(route('events.floorplans.preview', [$event->preview_token, $floorplan]))
            ->assertOk()
            ->assertSee('معاينة مخطط القاعة')
            ->assertSee('مخطط المعاينة')
            ->assertSee('طاولة 1')
            ->assertSee('ضيف المعاينة')
            ->assertDontSee('فتح المحرر');
    }

    public function test_floorplan_preview_returns_not_found_when_event_preview_is_disabled(): void
    {
        $event = Event::factory()->create([
            'preview_enabled' => false,
        ]);
        $floorplan = Floorplan::factory()->for($event)->create();

        $this->get(route('events.floorplans.preview', [$event->preview_token, $floorplan]))
            ->assertNotFound();
    }

    public function test_disabled_preview_link_returns_not_found(): void
    {
        $event = Event::factory()->create([
            'preview_enabled' => false,
        ]);

        $this->get(route('events.preview', $event->preview_token))
            ->assertNotFound();
    }

    public function test_admin_can_refresh_preview_token(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        $oldToken = $event->preview_token;

        $this->actingAs($user)
            ->post(route('events.preview-token.refresh', $event))
            ->assertRedirect(route('events.show', $event));

        $event->refresh();

        $this->assertNotSame($oldToken, $event->preview_token);
        $this->assertTrue($event->preview_enabled);
    }
}
