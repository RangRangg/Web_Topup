<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'PROMO'.Str::upper(Str::random(4)),
            'name' => 'Diskon Promo Game',
            'discount_type' => 'percentage',
            'discount_value' => fake()->randomElement([10, 15, 20, 25, 30]),
            'min_spend' => 50000,
            'max_discount' => 25000,
            'valid_from' => now()->subDays(5),
            'valid_until' => now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
