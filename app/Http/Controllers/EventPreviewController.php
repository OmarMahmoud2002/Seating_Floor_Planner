<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Floorplan;
use Illuminate\View\View;

class EventPreviewController extends Controller
{
    public function show(string $previewToken): View
    {
        $event = Event::query()
            ->where('preview_token', $previewToken)
            ->where('preview_enabled', true)
            ->firstOrFail();

        return view('events.preview', [
            'event' => $event,
        ]);
    }

    public function floorplan(string $previewToken, Floorplan $floorplan): View
    {
        $event = $this->resolvePreviewEvent($previewToken, $floorplan);

        $floorplan->load(['seats.guest.guestType']);
        $guestTypes = $floorplan->seats
            ->pluck('guest.guestType')
            ->filter()
            ->unique('id')
            ->sortBy('name_ar')
            ->values();

        return view('floorplans.preview', [
            'event' => $event,
            'floorplan' => $floorplan,
            'assignments' => $floorplan->seats->whereNotNull('guest_id')->keyBy('seat_key'),
            'guestTypes' => $guestTypes,
        ]);
    }

    private function resolvePreviewEvent(string $previewToken, Floorplan $floorplan): Event
    {
        $event = Event::query()
            ->where('preview_token', $previewToken)
            ->where('preview_enabled', true)
            ->firstOrFail();

        abort_unless($floorplan->event_id === $event->id, 404);

        return $event;
    }
}
