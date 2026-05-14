<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignSeatRequest;
use App\Http\Requests\SaveFloorplanLayoutRequest;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UnassignSeatRequest;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\GuestType;
use App\Models\Seat;
use App\Services\FloorPlanner\SeatingAssignmentService;
use App\Services\FloorPlanner\SeatBadgeResolver;
use App\Services\FloorPlanner\SeatSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FloorplanEditorController extends Controller
{
    public function show(Floorplan $floorplan): View
    {
        $this->authorize('view', $floorplan);

        return view('floorplans.editor', [
            'floorplan' => $floorplan,
            'event' => $floorplan->event,
            'editorConfig' => [
                'dataUrl' => route('editor.floorplans.data', $floorplan),
                'saveUrl' => route('editor.floorplans.save', $floorplan),
                'assignSeatUrl' => route('editor.floorplans.seats.assign', $floorplan),
                'unassignSeatUrl' => route('editor.floorplans.seats.unassign', $floorplan),
                'createGuestUrl' => route('editor.floorplans.guests.store', $floorplan),
                'updateGuestTypeUrl' => route('editor.floorplans.guests.type', [$floorplan, '__GUEST__']),
                'guestExportUrl' => route('events.guests.export', $floorplan->event),
                'pdfExportUrl' => route('floorplans.export.pdf', $floorplan),
                'previewUrl' => $floorplan->event->preview_enabled
                    ? route('events.floorplans.preview', [$floorplan->event->preview_token, $floorplan])
                    : null,
                'homeUrl' => route('dashboard'),
                'logoUrl' => asset('images/logo.png'),
                'backUrl' => route('events.show', $floorplan->event),
                'csrfToken' => csrf_token(),
            ],
        ]);
    }

    public function data(Floorplan $floorplan): JsonResponse
    {
        $this->authorize('view', $floorplan);

        return response()->json($this->payload($floorplan));
    }

    public function save(SaveFloorplanLayoutRequest $request, Floorplan $floorplan, SeatSyncService $seatSyncService): JsonResponse
    {
        $this->authorize('update', $floorplan);

        $designJson = $request->validated('design_json');

        DB::transaction(function () use ($floorplan, $designJson, $seatSyncService): void {
            $floorplan->update([
                'design_json' => $designJson,
                'last_saved_at' => now(),
            ]);

            $seatSyncService->sync($floorplan, $designJson);
        });

        return response()->json([
            'message' => 'تم حفظ المخطط بنجاح.',
            'last_saved_at' => $floorplan->last_saved_at->toIso8601String(),
        ]);
    }

    public function assignSeat(
        AssignSeatRequest $request,
        Floorplan $floorplan,
        SeatingAssignmentService $assignmentService
    ): JsonResponse {
        $this->authorize('update', $floorplan);

        $assignmentService->assign(
            $floorplan,
            (int) $request->validated('guest_id'),
            (string) $request->validated('seat_key')
        );

        return response()->json($this->payload($floorplan) + [
            'message' => 'تم تجليس الضيف بنجاح.',
        ]);
    }

    public function unassignSeat(
        UnassignSeatRequest $request,
        Floorplan $floorplan,
        SeatingAssignmentService $assignmentService
    ): JsonResponse {
        $this->authorize('update', $floorplan);

        $assignmentService->unassign($floorplan, (string) $request->validated('seat_key'));

        return response()->json($this->payload($floorplan) + [
            'message' => 'تم إزالة التجليس.',
        ]);
    }

    public function storeGuest(StoreGuestRequest $request, Floorplan $floorplan): JsonResponse
    {
        $this->authorize('update', $floorplan);

        $floorplan->event->guests()->create($request->validated());

        return response()->json($this->payload($floorplan) + [
            'message' => 'تم إضافة الضيف بنجاح.',
        ], 201);
    }

    public function updateGuestType(Request $request, Floorplan $floorplan, Guest $guest): JsonResponse
    {
        $this->authorize('update', $floorplan);

        abort_unless($guest->event_id === $floorplan->event_id, 403);

        $validated = $request->validate([
            'guest_type_id' => ['nullable', Rule::exists('guest_types', 'id')],
        ], [
            'guest_type_id.exists' => 'نوع الضيف المحدد غير صحيح.',
        ]);

        $guest->update([
            'guest_type_id' => $validated['guest_type_id'] ?? null,
        ]);

        return response()->json($this->payload($floorplan) + [
            'message' => 'تم تحديث نوع الضيف.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Floorplan $floorplan): array
    {
        $floorplan->refresh();
        $event = $floorplan->event;
        $seatBadges = app(SeatBadgeResolver::class);
        $seats = Seat::query()
            ->with(['guest.guestType'])
            ->where('floorplan_id', $floorplan->id)
            ->orderBy('table_key')
            ->orderBy('seat_number')
            ->get();

        return [
            'floorplan' => [
                'id' => $floorplan->id,
                'name' => $floorplan->name,
                'width' => (float) $floorplan->width,
                'height' => (float) $floorplan->height,
                'unit' => $floorplan->unit,
                'paper_size' => $floorplan->paper_size,
                'orientation' => $floorplan->orientation,
                'grid_size' => $floorplan->grid_size,
                'background_image_url' => $floorplan->backgroundImageUrl(),
                'design_json' => $floorplan->design_json ?: [
                    'version' => 1,
                    'elements' => [],
                    'viewport' => [
                        'scale' => 1,
                        'x' => 0,
                        'y' => 0,
                    ],
                ],
                'last_saved_at' => optional($floorplan->last_saved_at)->toIso8601String(),
            ],
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
            ],
            'assignments' => $this->seatAssignments($seats, $seatBadges),
            'guests' => $this->guestsPayload($floorplan, $seatBadges),
            'guest_types' => GuestType::query()
                ->orderBy('sort_order')
                ->orderBy('name_ar')
                ->get(['id', 'key', 'name_ar', 'color', 'icon'])
                ->map(fn (GuestType $type): array => [
                    'id' => $type->id,
                    'key' => $type->key,
                    'name_ar' => $type->display_name_ar,
                    'color' => $type->color,
                    'icon' => $type->icon,
                ]),
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Seat> $seats
     * @return array<string, array<string, mixed>>
     */
    private function seatAssignments($seats, SeatBadgeResolver $seatBadges): array
    {
        return $seats
            ->filter(fn (Seat $seat) => $seat->guest_id !== null)
            ->mapWithKeys(fn (Seat $seat) => [
                $seat->seat_key => [
                    'seat_key' => $seat->seat_key,
                    'table_key' => $seat->table_key,
                    'table_name' => $seat->table_name,
                    'seat_number' => $seat->seat_number,
                    'guest' => $seat->guest ? [
                        'id' => $seat->guest->id,
                        'name' => $seat->guest->name,
                        'status' => $seat->guest->status,
                        'gift_status' => $seat->guest->gift_status,
                        'checked_in_at' => optional($seat->guest->checked_in_at)->toIso8601String(),
                        'gift_used_at' => optional($seat->guest->gift_used_at)->toIso8601String(),
                        'seat_badges' => $seatBadges->forGuest($seat->guest),
                        'type' => $seat->guest->guestType ? [
                            'id' => $seat->guest->guestType->id,
                            'key' => $seat->guest->guestType->key,
                            'name_ar' => $seat->guest->guestType->display_name_ar,
                            'color' => $seat->guest->guestType->color,
                        ] : null,
                    ] : null,
                    'seat_badges' => $seatBadges->forGuest($seat->guest),
                ],
            ])
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function guestsPayload(Floorplan $floorplan, SeatBadgeResolver $seatBadges)
    {
        return $floorplan->event->guests()
            ->with(['guestType', 'seats' => fn ($query) => $query->where('floorplan_id', $floorplan->id)])
            ->orderBy('name')
            ->get()
            ->map(fn ($guest) => [
                'id' => $guest->id,
                'name' => $guest->name,
                'phone' => $guest->phone,
                'email' => $guest->email,
                'status' => $guest->status,
                'gift_status' => $guest->gift_status,
                'checked_in_at' => optional($guest->checked_in_at)->toIso8601String(),
                'gift_used_at' => optional($guest->gift_used_at)->toIso8601String(),
                'seat_badges' => $seatBadges->forGuest($guest),
                'type' => $guest->guestType ? [
                    'id' => $guest->guestType->id,
                    'key' => $guest->guestType->key,
                    'name_ar' => $guest->guestType->display_name_ar,
                    'color' => $guest->guestType->color,
                    'icon' => $guest->guestType->icon,
                ] : null,
                'assigned_seat' => $guest->seats->first() ? [
                    'seat_key' => $guest->seats->first()->seat_key,
                    'table_name' => $guest->seats->first()->table_name,
                    'seat_number' => $guest->seats->first()->seat_number,
                ] : null,
            ])
            ->values();
    }
}
