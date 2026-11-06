<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(35, 250) * 1000;

        return [
            'order_code' => 'AJ-'.Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'joki_profile_id' => null,
            'promotion_id' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_whatsapp' => fake()->phoneNumber(),
            'account_id' => (string) fake()->numberBetween(10000000, 99999999),
            'account_server' => (string) fake()->numberBetween(1000, 9999),
            'account_nickname' => fake()->userName(),
            'login_via' => 'Moonton ID',
            'account_notes' => 'Tolong main hero andalan ya kak.',
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
            'order_status' => fake()->randomElement(['pending', 'processing', 'in_review', 'completed']),
            'completed_at' => null,
        ];
    }
}
