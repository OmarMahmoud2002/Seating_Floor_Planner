<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFloorplanRequest;
use App\Http\Requests\UpdateFloorplanRequest;
use App\Models\Event;
use App\Models\Floorplan;
use App\Services\FloorPlanner\BackgroundImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FloorplanController extends Controller
{
    public function __construct(private readonly BackgroundImageService $backgroundImages)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $floorplans = Floorplan::query()
            ->with('event')
            ->whereHas('event', fn ($query) => $query->visibleTo($request->user()))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('event', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('location', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('floorplans.index', [
            'floorplans' => $floorplans,
            'search' => $search,
        ]);
    }

    public function create(Event $event): View
    {
        $this->authorize('view', $event);

        return view('floorplans.create', [
            'event' => $event,
            'floorplan' => new Floorplan([
                'unit' => 'meter',
                'paper_size' => 'A3',
                'orientation' => 'landscape',
                'grid_size' => 20,
            ]),
        ]);
    }

    public function store(StoreFloorplanRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('view', $event);

        $data = $request->validated();
        unset($data['background_image']);

        $data['grid_size'] = $data['grid_size'] ?? 20;

        if ($request->hasFile('background_image')) {
            $data['background_image_path'] = $this->backgroundImages->store($request->file('background_image'));
        }

        $floorplan = $event->floorplans()->create($data);

        return redirect()
            ->route('floorplans.editor', $floorplan)
            ->with('status', "تم إنشاء المخطط {$floorplan->name} بنجاح. يمكنك الآن البدء في ترتيب القاعة.");
    }

    public function edit(Floorplan $floorplan): View
    {
        $this->authorize('update', $floorplan);

        return view('floorplans.edit', [
            'event' => $floorplan->event,
            'floorplan' => $floorplan,
        ]);
    }

    public function update(UpdateFloorplanRequest $request, Floorplan $floorplan): RedirectResponse
    {
        $this->authorize('update', $floorplan);

        $data = $request->validated();
        unset($data['background_image'], $data['remove_background_image']);

        $data['grid_size'] = $data['grid_size'] ?? 20;

        if ($request->boolean('remove_background_image')) {
            $this->backgroundImages->delete($floorplan->background_image_path);
            $data['background_image_path'] = null;
        }

        if ($request->hasFile('background_image')) {
            $this->backgroundImages->delete($floorplan->background_image_path);
            $data['background_image_path'] = $this->backgroundImages->store($request->file('background_image'));
        }

        $floorplan->update($data);

        return redirect()
            ->route('events.show', $floorplan->event)
            ->with('status', 'تم تحديث المخطط بنجاح.');
    }

    public function destroy(Floorplan $floorplan): RedirectResponse
    {
        $this->authorize('delete', $floorplan);

        $event = $floorplan->event;

        $this->backgroundImages->delete($floorplan->background_image_path);
        $floorplan->delete();

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'تم حذف المخطط بنجاح.');
    }
}
