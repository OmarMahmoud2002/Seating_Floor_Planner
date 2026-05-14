<?php

namespace Tests\Unit;

use App\Models\Guest;
use App\Services\FloorPlanner\SeatBadgeResolver;
use PHPUnit\Framework\TestCase;

class SeatBadgeResolverTest extends TestCase
{
    public function test_empty_seat_has_no_badges(): void
    {
        $this->assertSame([], (new SeatBadgeResolver())->forGuest(null));
    }

    public function test_registered_guest_has_no_badges(): void
    {
        $guest = new Guest([
            'status' => 'registered',
            'gift_status' => 'not_used',
        ]);

        $this->assertSame([], (new SeatBadgeResolver())->forGuest($guest));
    }

    public function test_attended_guest_gets_attendance_badge(): void
    {
        $guest = new Guest([
            'status' => 'attended',
            'gift_status' => 'not_used',
        ]);

        $badges = (new SeatBadgeResolver())->forGuest($guest);

        $this->assertCount(1, $badges);
        $this->assertSame('attended', $badges[0]['key']);
        $this->assertSame('attendance', $badges[0]['type']);
    }

    public function test_gift_used_guest_gets_gift_badge(): void
    {
        $guest = new Guest([
            'status' => 'registered',
            'gift_status' => 'used',
        ]);

        $badges = (new SeatBadgeResolver())->forGuest($guest);

        $this->assertCount(1, $badges);
        $this->assertSame('gift_used', $badges[0]['key']);
        $this->assertSame('gift', $badges[0]['type']);
    }

    public function test_attended_guest_with_gift_gets_both_badges(): void
    {
        $guest = new Guest([
            'status' => 'Attended',
            'gift_status' => 'Used',
        ]);

        $badges = (new SeatBadgeResolver())->forGuest($guest);

        $this->assertSame(['attended', 'gift_used'], array_column($badges, 'key'));
    }
}
