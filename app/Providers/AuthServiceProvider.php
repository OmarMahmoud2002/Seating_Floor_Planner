<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Floorplan;
use App\Models\Guest;
use App\Policies\EventPolicy;
use App\Policies\FloorplanPolicy;
use App\Policies\GuestPolicy;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class => EventPolicy::class,
        Floorplan::class => FloorplanPolicy::class,
        Guest::class => GuestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
