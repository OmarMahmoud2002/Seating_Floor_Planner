<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestTypeRequest;
use App\Http\Requests\UpdateGuestTypeRequest;
use App\Models\GuestType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GuestTypeController extends Controller
{
    public function index(): View
    {
        return view('guest-types.index', [
            'guestType' => new GuestType([
                'color' => '#4D9B97',
                'sort_order' => 90,
            ]),
            'guestTypes' => GuestType::query()
                ->withCount('guests')
                ->orderBy('sort_order')
                ->orderBy('name_ar')
                ->get(),
        ]);
    }

    public function store(StoreGuestTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_default'] = false;

        GuestType::query()->create($data);

        return redirect()
            ->route('guest-types.index')
            ->with('status', 'تم إضافة نوع الضيف بنجاح.');
    }

    public function edit(GuestType $guestType): View
    {
        return view('guest-types.edit', [
            'guestType' => $guestType,
        ]);
    }

    public function update(UpdateGuestTypeRequest $request, GuestType $guestType): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $guestType->update($data);

        return redirect()
            ->route('guest-types.index')
            ->with('status', 'تم تحديث نوع الضيف بنجاح.');
    }

    public function destroy(GuestType $guestType): RedirectResponse
    {
        if ($guestType->guests()->exists()) {
            return redirect()
                ->route('guest-types.index')
                ->with('error', 'لا يمكن حذف نوع مرتبط بضيوف. غيّر نوع الضيوف أولا.');
        }

        $guestType->delete();

        return redirect()
            ->route('guest-types.index')
            ->with('status', 'تم حذف نوع الضيف بنجاح.');
    }
}
