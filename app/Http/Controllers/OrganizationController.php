<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $search = trim((string) $request->query('search'));

        $organizations = Organization::query()
            ->withCount(['users', 'events', 'guests'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $query->orWhere('external_user_id', $search);
                    }
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('organizations.index', [
            'organizations' => $organizations,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Organization $organization): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $organization->loadCount(['users', 'events', 'guests']);

        $users = $organization->users()
            ->latest()
            ->get();

        $events = $organization->events()
            ->withCount(['floorplans', 'guests'])
            ->latest('event_date')
            ->latest()
            ->limit(10)
            ->get();

        return view('organizations.show', [
            'organization' => $organization,
            'users' => $users,
            'events' => $events,
        ]);
    }
}
