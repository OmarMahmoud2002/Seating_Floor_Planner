<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Floorplan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FloorplanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_owned_floorplans_index(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownedFloorplan = Floorplan::factory()
            ->for(Event::factory()->for($user)->create(['name' => 'حدث خاص']))
            ->create(['name' => 'مخطط المستخدم']);
        $otherFloorplan = Floorplan::factory()
            ->for(Event::factory()->for($otherUser))
            ->create(['name' => 'مخطط مستخدم آخر']);

        $this->actingAs($user)
            ->get(route('floorplans.index'))
            ->assertOk()
            ->assertSee($ownedFloorplan->name)
            ->assertSee('حدث خاص')
            ->assertDontSee($otherFloorplan->name);
    }

    public function test_admin_can_create_floorplan_for_owned_event(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('events.floorplans.store', $event), [
            'name' => 'مخطط القاعة الرئيسية',
            'width' => 30,
            'height' => 20,
            'unit' => 'meter',
            'paper_size' => 'A4',
            'orientation' => 'landscape',
            'grid_size' => 20,
            'background_image' => UploadedFile::fake()->image('hall.jpg', 1200, 800)->size(600),
        ]);

        $floorplan = Floorplan::query()->first();

        $response->assertRedirect(route('floorplans.editor', $floorplan));

        $this->assertDatabaseHas('floorplans', [
            'event_id' => $event->id,
            'name' => 'مخطط القاعة الرئيسية',
            'unit' => 'meter',
        ]);
        $this->assertNotNull($floorplan->background_image_path);
        Storage::disk('public')->assertExists($floorplan->background_image_path);
    }

    public function test_create_floorplan_page_shows_setup_steps(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('events.floorplans.create', $event))
            ->assertOk()
            ->assertSee('بيانات الحدث')
            ->assertSee('إعداد المخطط')
            ->assertSee('محرر المخطط')
            ->assertSee('التالي: فتح المحرر');
    }

    public function test_floorplan_dimensions_are_required(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('events.floorplans.store', $event), [
            'name' => 'مخطط ناقص',
            'unit' => 'meter',
            'paper_size' => 'A4',
            'orientation' => 'landscape',
        ]);

        $response->assertSessionHasErrors(['width', 'height']);
    }

    public function test_admin_can_update_floorplan_and_remove_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create([
                'background_image_path' => 'floorplans/backgrounds/old.jpg',
            ]);

        Storage::disk('public')->put($floorplan->background_image_path, 'old image');

        $response = $this->actingAs($user)->put(route('floorplans.update', $floorplan), [
            'name' => 'مخطط محدث',
            'width' => 40,
            'height' => 25,
            'unit' => 'meter',
            'paper_size' => 'A3',
            'orientation' => 'portrait',
            'grid_size' => 25,
            'remove_background_image' => '1',
        ]);

        $response->assertRedirect(route('events.show', $floorplan->event));

        $this->assertDatabaseHas('floorplans', [
            'id' => $floorplan->id,
            'name' => 'مخطط محدث',
            'background_image_path' => null,
        ]);
        Storage::disk('public')->assertMissing('floorplans/backgrounds/old.jpg');
    }

    public function test_admin_cannot_edit_another_users_floorplan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($otherUser))
            ->create();

        $this->actingAs($user)
            ->get(route('floorplans.edit', $floorplan))
            ->assertForbidden();
    }

    public function test_admin_can_delete_floorplan_and_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create([
                'background_image_path' => 'floorplans/backgrounds/delete-me.jpg',
            ]);

        Storage::disk('public')->put($floorplan->background_image_path, 'old image');

        $this->actingAs($user)
            ->delete(route('floorplans.destroy', $floorplan))
            ->assertRedirect(route('events.show', $floorplan->event));

        $this->assertDatabaseMissing('floorplans', [
            'id' => $floorplan->id,
        ]);
        Storage::disk('public')->assertMissing('floorplans/backgrounds/delete-me.jpg');
    }
}
