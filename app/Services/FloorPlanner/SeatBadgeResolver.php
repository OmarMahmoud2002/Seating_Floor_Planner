<?php

namespace App\Services\FloorPlanner;

use App\Models\Guest;

class SeatBadgeResolver
{
    /**
     * @return array<int, array<string, string>>
     */
    public function forGuest(?Guest $guest): array
    {
        if (! $guest) {
            return [];
        }

        $badges = [];

        if ($this->isAttended($guest->status)) {
            $badges[] = [
                'key' => 'attended',
                'type' => 'attendance',
                'label' => 'حضر',
                'color' => '#00b894',
            ];
        }

        if ($this->isGiftUsed($guest->gift_status)) {
            $badges[] = [
                'key' => 'gift_used',
                'type' => 'gift',
                'label' => 'استلم الهدية',
                'color' => '#f97316',
            ];
        }

        return $badges;
    }

    private function isAttended(?string $status): bool
    {
        return in_array($this->normalize($status), ['attended'], true);
    }

    private function isGiftUsed(?string $status): bool
    {
        return in_array($this->normalize($status), ['used'], true);
    }

    private function normalize(?string $value): string
    {
        return str_replace([' ', '-'], '_', strtolower(trim((string) $value)));
    }
}
