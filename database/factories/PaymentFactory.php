<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->numberBetween(35, 200) * 1000;

        return [
            'order_id' => Order::factory(),
            'payment_code' => 'PAY-'.Str::upper(Str::random(8)),
            'payment_method' => fake()->randomElement(['qris', 'bca', 'mandiri', 'dana', 'gopay']),
            'amount' => $amount,
            'status' => 'pending',
            'payment_proof' => null,
            'paid_at' => null,
            'payload' => null,
        ];
    }
}
