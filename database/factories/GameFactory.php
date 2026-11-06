<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['moba', 'fps', 'battle_royale', 'rpg']),
            'publisher' => fake()->company(),
            'icon_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=300&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80',
            'description' => fake()->paragraph(),
            'badge' => fake()->randomElement(['HOT', 'POPULAR', 'NEW', null]),
            'is_active' => true,
            'order_priority' => fake()->numberBetween(1, 10),
        ];
    }
}
