<?php

namespace Tests\Feature;

use App\Exports\GuestListExport;
use App\Models\Event;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\GuestType;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_guest_list_excel(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('events.guests.export', $event))
            ->assertOk();

        Excel::assertDownloaded('guest-list-event-'.$event->id.'.xlsx');
    }

    public function test_guest_list_export_includes_table_and_seat(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        $floorplan = Floorplan::factory()->for($event)->create(['name' => 'المخطط الرئيسي']);
        $guestType = GuestType::factory()->create(['name_ar' => 'VIP']);
        $guest = Guest::factory()
            ->for($event)
            ->for($guestType)
            ->create([
                'name' => 'منى علي',
                'phone' => '01111111111',
                'email' => 'mona@example.com',
                'notes' => 'ملاحظة خاصة',
            ]);

        Seat::query()->create([
            'floorplan_id' => $floorplan->id,
            'guest_id' => $guest->id,
            'table_key' => 'table-1',
            'table_name' => 'طاولة كبار الضيوف',
            'seat_key' => 'table-1-seat-3',
            'seat_number' => 3,
            'x' => 100,
            'y' => 100,
            'rotation' => 0,
            'status' => 'occupied',
        ]);

        $export = new GuestListExport($event);
        $row = $export->map($export->collection()->first());

        $this->assertSame('منى علي', $row[0]);
        $this->assertSame('VIP', $row[1]);
        $this->assertSame('المخطط الرئيسي', $row[4]);
        $this->assertSame('طاولة كبار الضيوف', $row[5]);
        $this->assertSame('3', $row[6]);
    }

    public function test_admin_can_download_floorplan_pdf_from_canvas_image(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user)->create(['name' => 'حفل الشركة']))
            ->create(['name' => 'قاعة أ']);

        $guest = Guest::factory()->for($floorplan->event)->create();
        Seat::query()->create([
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

        $this->actingAs($user)
            ->post(route('floorplans.export.pdf', $floorplan), [
                'image_data' => $this->tinyPngDataUrl(),
            ])
            ->assertOk()
            ->assertDownload('floorplan-'.$floorplan->id.'.pdf');
    }

    public function test_floorplan_pdf_rejects_invalid_image_payload(): void
    {
        $user = User::factory()->create();
        $floorplan = Floorplan::factory()
            ->for(Event::factory()->for($user))
            ->create();

        $this->actingAs($user)
            ->post(route('floorplans.export.pdf', $floorplan), [
                'image_data' => 'not-an-image',
            ])
            ->assertSessionHasErrors('image_data');
    }

    public function test_admin_cannot_export_another_users_event_or_floorplan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->for($otherUser)->create();
        $floorplan = Floorplan::factory()->for($event)->create();

        $this->actingAs($user)
            ->get(route('events.guests.export', $event))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('floorplans.export.pdf', $floorplan), [
                'image_data' => $this->tinyPngDataUrl(),
            ])
            ->assertForbidden();
    }

    private function tinyPngDataUrl(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    }
}
