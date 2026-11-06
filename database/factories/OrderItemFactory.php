<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(35, 150) * 1000;

        return [
            'order_id' => Order::factory(),
            'game_product_id' => null,
            'joki_service_id' => null,
            'item_type' => 'joki_service',
            'item_name' => 'Joki Service Boost',
            'price' => $price,
            'quantity' => 1,
            'subtotal' => $price,
            'meta' => null,
        ];
    }
}
