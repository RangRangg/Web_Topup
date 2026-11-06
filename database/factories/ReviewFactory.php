<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'joki_profile_id' => null,
            'customer_name' => fake()->name(),
            'game_name' => 'Mobile Legends',
            'service_name' => 'Mythic Push Rank',
            'rating' => 5,
            'comment' => 'Pelayanan sangat cepat, ramah, dan booster jago banget! Sangat direkomendasikan.',
            'is_verified' => true,
        ];
    }
}
