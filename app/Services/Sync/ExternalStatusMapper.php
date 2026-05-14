<?php

namespace App\Services\Sync;

class ExternalStatusMapper
{
    public function guestStatus(?string $status): string
    {
        return match ($status) {
            'Attended', 'attended' => 'attended',
            'Submit', 'registered', null, '' => 'registered',
            default => $status,
        };
    }

    public function giftStatus(?string $status): string
    {
        return match ($status) {
            'Used', 'used' => 'used',
            'Not Used', 'not_used', null, '' => 'not_used',
            default => $status,
        };
    }
}
