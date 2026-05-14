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

class FloorplanEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_owned_floorplan_editor(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('floorplans.editor', $floorplan))
            ->assertOk()
            ->assertSee('floorplan-editor');
    }

    public function test_admin_can_load_editor_data(): void
    {
        $user = User::factory()->create();
        $guestType = GuestType::factory()->create([
            'key' => 'media',
            'name_ar' => 'media',
        ]);
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create([
                'design_json' => [
                    'version' => 1,
                    'elements' => [],
                    'viewport' => ['scale' => 1],
                ],
            ]);
        Guest::factory()
            ->for($floorplan->event)
            ->for($guestType)
            ->create(['name' => 'سارة محمد']);

        $this->actingAs($user)
            ->getJson(route('editor.floorplans.data', $floorplan))
            ->assertOk()
            ->assertJsonPath('floorplan.id', $floorplan->id)
            ->assertJsonPath('floorplan.design_json.version', 1)
            ->assertJsonPath('guests.0.name', 'سارة محمد')
            ->assertJsonPath('guests.0.type.key', 'media')
            ->assertJsonPath('guests.0.type.name_ar', 'media')
            ->assertJsonPath('guest_types.0.key', 'media');
    }

    public function test_editor_data_includes_guest_status_gift_status_and_seat_badges(): void
    {
        $user = User::factory()->create();
        $guestType = GuestType::factory()->create([
            'name_ar' => 'VIP',
            'color' => '#e7c539',
        ]);
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $guest = Guest::factory()
            ->for($floorplan->event)
            ->for($guestType)
            ->create([
                'name' => 'Mona Salem',
                'status' => 'attended',
                'gift_status' => 'used',
                'checked_in_at' => now(),
                'gift_used_at' => now(),
            ]);
        $seat = $this->createSeat($floorplan, [
            'guest_id' => $guest->id,
            'status' => 'occupied',
        ]);

        $this->actingAs($user)
            ->getJson(route('editor.floorplans.data', $floorplan))
            ->assertOk()
            ->assertJsonPath("assignments.{$seat->seat_key}.guest.status", 'attended')
            ->assertJsonPath("assignments.{$seat->seat_key}.guest.gift_status", 'used')
            ->assertJsonPath("assignments.{$seat->seat_key}.guest.type.color", '#e7c539')
            ->assertJsonPath("assignments.{$seat->seat_key}.seat_badges.0.key", 'attended')
            ->assertJsonPath("assignments.{$seat->seat_key}.seat_badges.1.key", 'gift_used')
            ->assertJsonPath('guests.0.status', 'attended')
            ->assertJsonPath('guests.0.gift_status', 'used')
            ->assertJsonPath('guests.0.seat_badges.0.type', 'attendance')
            ->assertJsonPath('guests.0.seat_badges.1.type', 'gift');
    }

    public function test_admin_can_create_guest_from_editor(): void
    {
        $user = User::factory()->create();
        $guestType = GuestType::factory()->create(['name_ar' => 'VIP']);
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.guests.store', $floorplan), [
                'name' => 'ضيف جديد',
                'guest_type_id' => $guestType->id,
                'phone' => '01000000000',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'تم إضافة الضيف بنجاح.')
            ->assertJsonPath('guests.0.name', 'ضيف جديد')
            ->assertJsonPath('guests.0.type.name_ar', 'VIP');

        $this->assertDatabaseHas('guests', [
            'event_id' => $floorplan->event_id,
            'name' => 'ضيف جديد',
            'guest_type_id' => $guestType->id,
        ]);
    }

    public function test_admin_can_save_floorplan_design_json_and_sync_seats(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();

        $payload = [
            'design_json' => [
                'version' => 1,
                'elements' => [
                    [
                        'id' => 'table-1',
                        'type' => 'table',
                        'label' => 'طاولة 1',
                        'x' => 120,
                        'y' => 140,
                        'width' => 170,
                        'height' => 88,
                        'tableShape' => 'rectangle',
                        'seatCount' => 2,
                        'seats' => [
                            [
                                'key' => 'table-1-seat-1',
                                'number' => 1,
                                'label' => '1',
                                'x' => 20,
                                'y' => -28,
                                'rotation' => 0,
                            ],
                            [
                                'key' => 'table-1-seat-2',
                                'number' => 2,
                                'label' => '2',
                                'x' => 150,
                                'y' => 116,
                                'rotation' => 180,
                            ],
                        ],
                    ],
                ],
                'viewport' => [
                    'scale' => 1,
                    'x' => 0,
                    'y' => 0,
                ],
            ],
        ];

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.save', $floorplan), $payload)
            ->assertOk()
            ->assertJsonPath('message', 'تم حفظ المخطط بنجاح.');

        $floorplan->refresh();

        $this->assertSame('table-1', $floorplan->design_json['elements'][0]['id']);
        $this->assertNotNull($floorplan->last_saved_at);
        $this->assertDatabaseHas('seats', [
            'floorplan_id' => $floorplan->id,
            'table_key' => 'table-1',
            'seat_key' => 'table-1-seat-1',
            'seat_number' => 1,
            'x' => 140,
            'y' => 112,
        ]);
        $this->assertDatabaseHas('seats', [
            'floorplan_id' => $floorplan->id,
            'table_key' => 'table-1',
            'seat_key' => 'table-1-seat-2',
            'seat_number' => 2,
            'x' => 270,
            'y' => 256,
        ]);
    }

    public function test_saving_floorplan_removes_deleted_seats(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();

        $firstPayload = [
            'design_json' => [
                'version' => 1,
                'elements' => [
                    [
                        'id' => 'table-1',
                        'type' => 'table',
                        'label' => 'طاولة 1',
                        'x' => 100,
                        'y' => 100,
                        'width' => 160,
                        'height' => 90,
                        'tableShape' => 'rectangle',
                        'seatCount' => 2,
                        'seats' => [
                            ['key' => 'table-1-seat-1', 'number' => 1, 'x' => 0, 'y' => 0],
                            ['key' => 'table-1-seat-2', 'number' => 2, 'x' => 20, 'y' => 0],
                        ],
                    ],
                ],
                'viewport' => ['scale' => 1],
            ],
        ];

        $secondPayload = $firstPayload;
        $secondPayload['design_json']['elements'][0]['seatCount'] = 1;
        $secondPayload['design_json']['elements'][0]['seats'] = [
            ['key' => 'table-1-seat-1', 'number' => 1, 'x' => 10, 'y' => 10],
        ];

        $this->actingAs($user)->postJson(route('editor.floorplans.save', $floorplan), $firstPayload)->assertOk();
        $this->actingAs($user)->postJson(route('editor.floorplans.save', $floorplan), $secondPayload)->assertOk();

        $this->assertDatabaseHas('seats', [
            'floorplan_id' => $floorplan->id,
            'seat_key' => 'table-1-seat-1',
            'x' => 110,
            'y' => 110,
        ]);
        $this->assertDatabaseMissing('seats', [
            'floorplan_id' => $floorplan->id,
            'seat_key' => 'table-1-seat-2',
        ]);
    }

    public function test_admin_can_save_generic_floorplan_elements_and_viewport(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();

        $payload = [
            'design_json' => [
                'version' => 1,
                'elements' => [
                    [
                        'id' => 'stage-1',
                        'type' => 'stage',
                        'label' => 'المسرح الرئيسي',
                        'x' => 80,
                        'y' => 60,
                        'width' => 260,
                        'height' => 90,
                        'rotation' => 5,
                        'fill' => '#EAF4FB',
                        'stroke' => '#31719D',
                        'opacity' => 0.8,
                    ],
                    [
                        'id' => 'wall-1',
                        'type' => 'wall',
                        'label' => 'حائط',
                        'x' => 40,
                        'y' => 220,
                        'width' => 420,
                        'height' => 16,
                        'rotation' => 0,
                        'fill' => '#344054',
                        'stroke' => '#1F2937',
                    ],
                ],
                'viewport' => [
                    'scale' => 1.4,
                    'x' => 24,
                    'y' => -12,
                ],
            ],
        ];

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.save', $floorplan), $payload)
            ->assertOk();

        $floorplan->refresh();

        $this->assertSame('stage', $floorplan->design_json['elements'][0]['type']);
        $this->assertSame(0.8, $floorplan->design_json['elements'][0]['opacity']);
        $this->assertSame(1.4, $floorplan->design_json['viewport']['scale']);
        $this->assertSame(24, $floorplan->design_json['viewport']['x']);
        $this->assertSame(-12, $floorplan->design_json['viewport']['y']);
        $this->assertDatabaseCount('seats', 0);
    }

    public function test_admin_can_assign_guest_to_seat(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $guest = Guest::factory()->for($floorplan->event)->create(['name' => 'أحمد سالم']);
        $seat = $this->createSeat($floorplan);

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.seats.assign', $floorplan), [
                'guest_id' => $guest->id,
                'seat_key' => $seat->seat_key,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم تجليس الضيف بنجاح.')
            ->assertJsonPath("assignments.{$seat->seat_key}.guest.name", 'أحمد سالم');

        $this->assertDatabaseHas('seats', [
            'id' => $seat->id,
            'guest_id' => $guest->id,
            'status' => 'occupied',
        ]);
    }

    public function test_admin_can_update_guest_type_from_editor(): void
    {
        $user = User::factory()->create();
        $guestType = GuestType::factory()->create(['name_ar' => 'VIP']);
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $guest = Guest::factory()->for($floorplan->event)->create(['guest_type_id' => null]);

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.guests.type', [$floorplan, $guest]), [
                'guest_type_id' => $guestType->id,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم تحديث نوع الضيف.')
            ->assertJsonPath('guests.0.type.name_ar', 'VIP');

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'guest_type_id' => $guestType->id,
        ]);
    }

    public function test_assigning_guest_to_new_seat_removes_old_assignment(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $guest = Guest::factory()->for($floorplan->event)->create();
        $firstSeat = $this->createSeat($floorplan, [
            'guest_id' => $guest->id,
            'status' => 'occupied',
        ]);
        $secondSeat = $this->createSeat($floorplan, [
            'seat_key' => 'table-1-seat-2',
            'seat_number' => 2,
            'x' => 130,
        ]);

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.seats.assign', $floorplan), [
                'guest_id' => $guest->id,
                'seat_key' => $secondSeat->seat_key,
            ])
            ->assertOk();

        $this->assertDatabaseHas('seats', [
            'id' => $firstSeat->id,
            'guest_id' => null,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('seats', [
            'id' => $secondSeat->id,
            'guest_id' => $guest->id,
            'status' => 'occupied',
        ]);
    }

    public function test_occupied_seat_cannot_receive_another_guest(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $firstGuest = Guest::factory()->for($floorplan->event)->create();
        $secondGuest = Guest::factory()->for($floorplan->event)->create();
        $seat = $this->createSeat($floorplan, [
            'guest_id' => $firstGuest->id,
            'status' => 'occupied',
        ]);

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.seats.assign', $floorplan), [
                'guest_id' => $secondGuest->id,
                'seat_key' => $seat->seat_key,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('seat_key');
    }

    public function test_guest_from_another_event_cannot_be_assigned(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $otherGuest = Guest::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $seat = $this->createSeat($floorplan);

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.seats.assign', $floorplan), [
                'guest_id' => $otherGuest->id,
                'seat_key' => $seat->seat_key,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('guest_id');
    }

    public function test_admin_can_unassign_seat(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();
        $guest = Guest::factory()->for($floorplan->event)->create();
        $seat = $this->createSeat($floorplan, [
            'guest_id' => $guest->id,
            'status' => 'occupied',
        ]);

        $this->actingAs($user)
            ->postJson(route('editor.floorplans.seats.unassign', $floorplan), [
                'seat_key' => $seat->seat_key,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم إزالة التجليس.');

        $this->assertDatabaseHas('seats', [
            'id' => $seat->id,
            'guest_id' => null,
            'status' => 'available',
        ]);
    }

    public function test_admin_cannot_open_another_users_editor(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($otherUser))
            ->create();

        $this->actingAs($user)
            ->get(route('floorplans.editor', $floorplan))
            ->assertForbidden();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createSeat(Floorplan $floorplan, array $overrides = []): Seat
    {
        return Seat::query()->create($overrides + [
            'floorplan_id' => $floorplan->id,
            'table_key' => 'table-1',
            'table_name' => 'طاولة 1',
            'seat_key' => 'table-1-seat-1',
            'seat_number' => 1,
            'x' => 100,
            'y' => 100,
            'rotation' => 0,
        ]);
    }
}
