<?php

namespace Database\Factories;

use App\Models\GuestType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuestType>
 */
class GuestTypeFactory extends Factory
{
    protected $model = GuestType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_ar' => fake()->randomElement(['عادي', 'VIP', 'VVIP', 'media']),
            'color' => fake()->hexColor(),
            'icon' => 'user',
            'sort_order' => fake()->numberBetween(1, 100),
            'is_default' => false,
        ];
    }
}
