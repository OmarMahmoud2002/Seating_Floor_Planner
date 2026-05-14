<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    public function update(User $user, Guest $guest): bool
    {
        return $guest->event !== null && $user->canManageEvent($guest->event);
    }

    public function delete(User $user, Guest $guest): bool
    {
        return $guest->event !== null && $user->canManageEvent($guest->event);
    }
}
