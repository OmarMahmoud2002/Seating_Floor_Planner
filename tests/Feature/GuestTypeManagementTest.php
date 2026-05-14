<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_guest_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('guest-types.store'), [
                'name_ar' => 'إعلام',
                'color' => '#4596CF',
                'icon' => 'camera',
                'sort_order' => 25,
            ])
            ->assertRedirect(route('guest-types.index'));

        $this->assertDatabaseHas('guest_types', [
            'name_ar' => 'إعلام',
            'color' => '#4596CF',
            'sort_order' => 25,
        ]);
    }

    public function test_guest_type_color_must_be_hex(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('guest-types.store'), [
                'name_ar' => 'نوع',
                'color' => 'blue',
            ])
            ->assertSessionHasErrors('color');
    }

    public function test_admin_can_update_guest_type(): void
    {
        $user = User::factory()->create();
        $guestType = GuestType::factory()->create(['name_ar' => 'قديم']);

        $this->actingAs($user)
            ->put(route('guest-types.update', $guestType), [
                'name_ar' => 'محدث',
                'color' => '#4D9B97',
                'icon' => 'user',
                'sort_order' => 5,
            ])
            ->assertRedirect(route('guest-types.index'));

        $this->assertDatabaseHas('guest_types', [
            'id' => $guestType->id,
            'name_ar' => 'محدث',
        ]);
    }

    public function test_guest_type_with_guests_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $guestType = GuestType::factory()->create();
        Guest::factory()->for($guestType)->create();

        $this->actingAs($user)
            ->delete(route('guest-types.destroy', $guestType))
            ->assertRedirect(route('guest-types.index'));

        $this->assertDatabaseHas('guest_types', [
            'id' => $guestType->id,
        ]);
    }
}
