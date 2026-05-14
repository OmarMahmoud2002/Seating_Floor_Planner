<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
use App\Models\Event;
use App\Models\Guest;
use App\Models\GuestType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        $this->authorize('view', $event);

        $search = trim((string) $request->query('search'));
        $typeId = $request->query('type');

        $guests = $event->guests()
            ->with(['guestType', 'seats.floorplan'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($typeId, fn ($query) => $query->where('guest_type_id', $typeId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('guests.index', [
            'event' => $event,
            'guest' => new Guest(),
            'guests' => $guests,
            'guestTypes' => GuestType::query()->orderBy('sort_order')->orderBy('name_ar')->get(),
            'search' => $search,
            'typeId' => $typeId,
        ]);
    }

    public function store(StoreGuestRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('view', $event);

        $event->guests()->create($request->validated());

        return redirect()
            ->route('events.guests.index', $event)
            ->with('status', 'تم إضافة الضيف بنجاح.');
    }

    public function edit(Guest $guest): View
    {
        $this->authorize('update', $guest);

        return view('guests.edit', [
            'guest' => $guest->load('event'),
            'event' => $guest->event,
            'guestTypes' => GuestType::query()->orderBy('sort_order')->orderBy('name_ar')->get(),
        ]);
    }

    public function update(UpdateGuestRequest $request, Guest $guest): RedirectResponse
    {
        $this->authorize('update', $guest);

        $guest->update($request->validated());

        return redirect()
            ->route('events.guests.index', $guest->event)
            ->with('status', 'تم تحديث بيانات الضيف بنجاح.');
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        $this->authorize('delete', $guest);

        $event = $guest->event;
        $guest->delete();

        return redirect()
            ->route('events.guests.index', $event)
            ->with('status', 'تم حذف الضيف بنجاح.');
    }
}
