<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GameProduct>
 */
class GameProductFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomElement([50, 100, 250, 500, 1000]);
        $name = $amount.' Diamonds / Points';
        $price = $amount * 250;

        return [
            'game_id' => Game::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.Str::random(5)),
            'code' => 'PROD-'.Str::upper(Str::random(6)),
            'category' => fake()->randomElement(['diamond', 'points', 'uc', 'pass', 'voucher']),
            'price' => $price,
            'original_price' => $price * 1.2,
            'stock' => 999,
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }
}
