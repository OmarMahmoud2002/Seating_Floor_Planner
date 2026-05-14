<?php

namespace Tests\Unit;

use App\Models\Floorplan;
use PHPUnit\Framework\TestCase;

class FloorplanModelTest extends TestCase
{
    public function test_background_image_url_is_same_origin_relative_path(): void
    {
        $floorplan = new Floorplan([
            'background_image_path' => 'floorplans/backgrounds/example.png',
        ]);

        $this->assertSame('/storage/floorplans/backgrounds/example.png', $floorplan->backgroundImageUrl());
    }
}
