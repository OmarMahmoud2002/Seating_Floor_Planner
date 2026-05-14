<?php

namespace App\Services\Sync;

use App\Models\GuestType;

class GuestTypeMapper
{
    public function fromKey(string $guestTypeKey): GuestType
    {
        return GuestType::query()
            ->whereNull('organization_id')
            ->where('key', $guestTypeKey)
            ->firstOrFail();
    }
}
