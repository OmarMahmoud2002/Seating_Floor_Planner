<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $search = trim((string) $request->query('search'));

        $events = Event::query()
            ->visibleTo($request->user())
            ->withCount('floorplans')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->latest('event_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'search' => $search,
            'guestTypeLinkTypes' => $this->guestTypeLinkTypes(),
            'eventRegistrationLinks' => $events->getCollection()
                ->mapWithKeys(fn (Event $event): array => [$event->id => $this->guestTypeLinks($event)])
                ->all(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('events.create', [
            'event' => new Event([
                'preview_enabled' => true,
            ]),
            'guestTypeLinkTypes' => $this->guestTypeLinkTypes(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $data = $request->validated();
        $data = $this->fillRegistrationLinkOptions($request, $data);
        $data['preview_enabled'] = $request->boolean('preview_enabled') || $this->hasRegistrationLinkEnabled($data);

        $event = $request->user()->events()->create($data);

        return redirect()
            ->route('events.floorplans.create', $event)
            ->with('status', 'تم إنشاء الحدث بنجاح. أضف أول مخطط للانتقال إلى المحرر.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'floorplans' => fn ($query) => $query->withCount('seats')->latest(),
        ]);
        $event->loadCount(['floorplans', 'guests']);

        return view('events.show', [
            'event' => $event,
            'previewUrl' => route('events.preview', $event->preview_token),
            'guestTypeLinks' => $this->guestTypeLinks($event),
        ]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('events.edit', [
            'event' => $event,
            'guestTypeLinkTypes' => $this->guestTypeLinkTypes(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $data = $request->validated();
        $data = $this->fillRegistrationLinkOptions($request, $data);
        $data['preview_enabled'] = $request->boolean('preview_enabled') || $this->hasRegistrationLinkEnabled($data);

        $event->update($data);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'تم تحديث الحدث بنجاح.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('status', 'تم حذف الحدث بنجاح.');
    }

    public function refreshPreviewToken(Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->forceFill([
            'preview_token' => Event::newPreviewToken(),
            'preview_enabled' => true,
        ])->save();

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'تم إنشاء رابط معاينة جديد.');
    }

    /**
     * @return array<int, array{key: string, label: string, color: string, url: string}>
     */
    private function guestTypeLinks(Event $event): array
    {
        return array_values(array_map(
            fn (array $type): array => $type + [
                'url' => route('events.preview', [
                    'previewToken' => $event->preview_token,
                    'guest_type_key' => $type['key'],
                ]),
            ],
            array_filter(
                $this->guestTypeLinkTypes(),
                fn (array $type): bool => $this->registrationLinkEnabled($event, $type['key'])
            )
        ));
    }

    /**
     * @return array<int, array{key: string, label: string, color: string}>
     */
    private function guestTypeLinkTypes(): array
    {
        return [
            [
                'key' => 'vip',
                'label' => 'VIP',
                'color' => '#D97706',
            ],
            [
                'key' => 'vvip',
                'label' => 'VVIP',
                'color' => '#7C3AED',
            ],
            [
                'key' => 'media',
                'label' => 'media',
                'color' => '#0284C7',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function fillRegistrationLinkOptions(Request $request, array $data): array
    {
        foreach ($this->guestTypeLinkTypes() as $type) {
            $data[$this->registrationLinkColumn($type['key'])] = $request->boolean($this->registrationLinkColumn($type['key']));
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hasRegistrationLinkEnabled(array $data): bool
    {
        foreach ($this->guestTypeLinkTypes() as $type) {
            if (! empty($data[$this->registrationLinkColumn($type['key'])])) {
                return true;
            }
        }

        return false;
    }

    private function registrationLinkEnabled(Event $event, string $key): bool
    {
        return (bool) $event->getAttribute($this->registrationLinkColumn($key));
    }

    private function registrationLinkColumn(string $key): string
    {
        return "{$key}_registration_enabled";
    }
}
