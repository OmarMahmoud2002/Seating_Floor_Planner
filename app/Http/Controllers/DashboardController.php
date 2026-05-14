<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $eventsQuery = Event::query()->visibleTo($user);
        $totalEvents = (clone $eventsQuery)->count();
        $upcomingEvents = (clone $eventsQuery)
            ->whereDate('event_date', '>=', Carbon::today())
            ->count();
        $totalFloorplans = Floorplan::query()
            ->whereHas('event', fn ($query) => $query->visibleTo($user))
            ->count();
        $totalGuests = Guest::query()
            ->whereHas('event', fn ($query) => $query->visibleTo($user))
            ->count();
        $recentEvents = (clone $eventsQuery)
            ->with('organization')
            ->withCount(['floorplans', 'guests'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            [
                'label' => 'إجمالي الأحداث',
                'value' => $totalEvents,
                'hint' => 'عدد الأحداث المسجلة في النظام.',
            ],
            [
                'label' => 'الأحداث القادمة',
                'value' => $upcomingEvents,
                'hint' => 'الأحداث التي تاريخها اليوم أو لاحقا.',
            ],
            [
                'label' => 'إجمالي الضيوف',
                'value' => $totalGuests,
                'hint' => 'عدد الضيوف المسجلين في أحداثك.',
            ],
            [
                'label' => 'المخططات المحفوظة',
                'value' => $totalFloorplans,
                'hint' => 'ابدأ بإنشاء حدث ثم مخطط قاعة.',
            ],
        ];

        if ($isSuperAdmin) {
            array_unshift($stats, [
                'label' => 'المنظمات',
                'value' => Organization::query()->count(),
                'hint' => 'كل المنظمات المحلية والمتزامنة داخل محرك التجليس.',
            ]);
        }

        return view('dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
