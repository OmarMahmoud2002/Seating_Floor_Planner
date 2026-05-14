<?php

namespace App\Policies;

use App\Models\Floorplan;
use App\Models\User;

class FloorplanPolicy
{
    public function view(User $user, Floorplan $floorplan): bool
    {
        return $floorplan->event !== null && $user->canAccessEvent($floorplan->event);
    }

    public function update(User $user, Floorplan $floorplan): bool
    {
        return $floorplan->event !== null && $user->canManageEvent($floorplan->event);
    }

    public function delete(User $user, Floorplan $floorplan): bool
    {
        return $floorplan->event !== null && $user->canManageEvent($floorplan->event);
    }
}
