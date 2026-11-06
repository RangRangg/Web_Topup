<?php

namespace Database\Factories;

use App\Models\JokiProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JokiProfile>
 */
class JokiProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->joki(),
            'nickname' => 'Booster '.fake()->firstName(),
            'phone' => fake()->phoneNumber(),
            'bio' => 'Top Global Booster berlisensi dengan winrate 90%+. Siap bantu rank apa saja.',
            'specialized_games' => ['Mobile Legends', 'Valorant', 'Honor of Kings'],
            'highest_rank' => 'Mythical Immortal / Radiant',
            'rating' => fake()->randomFloat(2, 4.8, 5.0),
            'completed_orders_count' => fake()->numberBetween(10, 500),
            'status' => 'available',
            'is_verified' => true,
        ];
    }
}
