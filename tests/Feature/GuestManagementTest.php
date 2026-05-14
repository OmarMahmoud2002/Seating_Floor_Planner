<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use App\Models\GuestType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class GuestManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_guest_for_owned_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        $guestType = GuestType::factory()->create(['name_ar' => 'VIP']);

        $response = $this->actingAs($user)->post(route('events.guests.store', $event), [
            'guest_type_id' => $guestType->id,
            'name' => 'أحمد سالم',
            'phone' => '01000000000',
            'email' => 'ahmed@example.com',
            'notes' => 'ضيف مهم',
        ]);

        $response->assertRedirect(route('events.guests.index', $event));

        $this->assertDatabaseHas('guests', [
            'event_id' => $event->id,
            'guest_type_id' => $guestType->id,
            'name' => 'أحمد سالم',
        ]);
    }

    public function test_guest_name_is_required(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('events.guests.store', $event), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_and_delete_owned_guest(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()
            ->for(Event::factory()->for($user))
            ->create(['name' => 'ضيف قديم']);

        $this->actingAs($user)
            ->put(route('guests.update', $guest), [
                'name' => 'ضيف محدث',
                'phone' => '',
                'email' => '',
                'notes' => '',
            ])
            ->assertRedirect(route('events.guests.index', $guest->event));

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'ضيف محدث',
        ]);

        $this->actingAs($user)
            ->delete(route('guests.destroy', $guest))
            ->assertRedirect(route('events.guests.index', $guest->event));

        $this->assertDatabaseMissing('guests', [
            'id' => $guest->id,
        ]);
    }

    public function test_admin_cannot_edit_another_users_guest(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $guest = Guest::factory()
            ->for(Event::factory()->for($otherUser))
            ->create();

        $this->actingAs($user)
            ->get(route('guests.edit', $guest))
            ->assertForbidden();
    }

    public function test_event_page_shows_guest_management_summary(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        Guest::factory()->for($event)->create(['name' => 'منى علي']);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('1 ضيف')
            ->assertSee(route('events.guests.index', $event), false)
            ->assertDontSee('منى علي');
    }

    public function test_admin_can_preview_and_confirm_guest_excel_import(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        $vipType = GuestType::factory()->create(['name_ar' => 'VIP', 'color' => '#E7C539']);

        Guest::factory()->for($event)->create([
            'guest_type_id' => $vipType->id,
            'name' => 'ضيف موجود',
            'phone' => '01000000000',
            'email' => null,
        ]);

        $response = $this->actingAs($user)->post(route('events.guests.import.preview', $event), [
            'file' => $this->excelUpload([
                ['منى علي', '01111111111', 'mona@example.com', 'VIP', 'ملاحظة'],
                ['', '01222222222', 'missing@example.com', 'VIP', 'بدون اسم'],
                ['أحمد مكرر', '01000000000', '', 'VIP', 'نفس رقم موجود'],
                ['سارة مصطفى', '', 'sara@example.com', 'إعلام خاص', 'نوع جديد'],
            ]),
        ]);

        $response
            ->assertOk()
            ->assertSee('معاينة الصفوف')
            ->assertSee('اسم الضيف مفقود')
            ->assertSee('مكرر')
            ->assertSee('إعلام خاص');

        $this->assertDatabaseMissing('guests', [
            'event_id' => $event->id,
            'name' => 'منى علي',
        ]);

        $token = array_key_first(session('guest_imports'));

        $this->actingAs($user)
            ->post(route('events.guests.import.store', $event), ['token' => $token])
            ->assertRedirect(route('events.guests.index', $event));

        $this->assertDatabaseHas('guests', [
            'event_id' => $event->id,
            'name' => 'منى علي',
            'guest_type_id' => $vipType->id,
        ]);

        $this->assertDatabaseHas('guests', [
            'event_id' => $event->id,
            'name' => 'سارة مصطفى',
            'email' => 'sara@example.com',
        ]);

        $this->assertDatabaseHas('guest_types', [
            'name_ar' => 'إعلام خاص',
            'is_default' => false,
        ]);

        $this->assertSame(
            1,
            Guest::query()
                ->where('event_id', $event->id)
                ->where('phone', '01000000000')
                ->count()
        );
    }

    public function test_guest_import_rejects_invalid_file_type(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('events.guests.import.preview', $event), [
                'file' => UploadedFile::fake()->create('guests.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_guest_import_accepts_mobile_heading_as_phone(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $this->actingAs($user)->post(route('events.guests.import.preview', $event), [
            'file' => $this->excelUpload(
                [['ضيف من ملف', '0509353775', 'mobile@example.com', '', '']],
                'guests-mobile.xlsx',
                ['name', 'Mobile', 'Email', 'type', 'notes']
            ),
        ])->assertOk();

        $token = array_key_first(session('guest_imports'));

        $this->actingAs($user)
            ->post(route('events.guests.import.store', $event), ['token' => $token])
            ->assertRedirect(route('events.guests.index', $event));

        $this->assertDatabaseHas('guests', [
            'event_id' => $event->id,
            'name' => 'ضيف من ملف',
            'phone' => '0509353775',
        ]);
    }

    public function test_admin_cannot_import_guests_for_another_users_event(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->post(route('events.guests.import.preview', $event), [
                'file' => $this->excelUpload([
                    ['ضيف', '', '', '', ''],
                ]),
            ])
            ->assertForbidden();
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function excelUpload(
        array $rows,
        string $filename = 'guests.xlsx',
        array $headers = ['name', 'phone', 'email', 'type', 'notes']
    ): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = array_merge([$headers], $rows);

        foreach ($allRows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndex + 1).($rowIndex + 1),
                    $value
                );
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'guest_import_');
        $path = $tempPath.'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        @unlink($tempPath);

        return new UploadedFile(
            $path,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
