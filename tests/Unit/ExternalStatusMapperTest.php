<?php

namespace Tests\Unit;

use App\Services\Sync\ExternalStatusMapper;
use PHPUnit\Framework\TestCase;

class ExternalStatusMapperTest extends TestCase
{
    public function test_it_maps_eventos_guest_status_values_to_internal_values(): void
    {
        $mapper = new ExternalStatusMapper();

        $this->assertSame('registered', $mapper->guestStatus('Submit'));
        $this->assertSame('attended', $mapper->guestStatus('Attended'));
        $this->assertSame('registered', $mapper->guestStatus(null));
    }

    public function test_it_maps_eventos_gift_status_values_to_internal_values(): void
    {
        $mapper = new ExternalStatusMapper();

        $this->assertSame('not_used', $mapper->giftStatus('Not Used'));
        $this->assertSame('used', $mapper->giftStatus('Used'));
        $this->assertSame('not_used', $mapper->giftStatus(null));
    }
}
