<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\JokiService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JokiService>
 */
class JokiServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Push Rank '.fake()->word().' ke '.fake()->word();
        $price = fake()->numberBetween(30, 200) * 1000;

        return [
            'game_id' => Game::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.Str::random(5)),
            'service_type' => fake()->randomElement(['rank', 'winrate', 'placement', 'mabar', 'quest']),
            'rank_from' => 'Tier A',
            'rank_to' => 'Tier B',
            'price' => $price,
            'original_price' => $price * 1.25,
            'unit' => 'Paket',
            'estimated_duration' => '2-6 Jam',
            'is_popular' => fake()->boolean(40),
            'is_active' => true,
            'description' => fake()->paragraph(),
            'features' => ['Garansi Winrate 90%+', 'Pro Player Verified', 'Anti Minus & Screenshot'],
        ];
    }
}
