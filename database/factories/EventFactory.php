<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement(['مؤتمر', 'حفل', 'اجتماع']),
            'event_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'location' => fake()->city(),
            'description' => fake()->paragraph(),
            'preview_enabled' => true,
            'vip_registration_enabled' => false,
            'vvip_registration_enabled' => false,
            'media_registration_enabled' => false,
        ];
    }
}
