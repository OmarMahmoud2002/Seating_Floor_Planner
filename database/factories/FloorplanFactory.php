<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Floorplan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Floorplan>
 */
class FloorplanFactory extends Factory
{
    protected $model = Floorplan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => 'مخطط '.fake()->word(),
            'width' => 30,
            'height' => 20,
            'unit' => 'meter',
            'paper_size' => 'A3',
            'orientation' => 'landscape',
            'grid_size' => 20,
        ];
    }
}
