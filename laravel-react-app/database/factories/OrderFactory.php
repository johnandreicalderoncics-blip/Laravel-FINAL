<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_date' => now()->toDateString(),
            'total_price' => fake()->randomFloat(2, 100, 5000),
            'order_status' => Order::STATUS_PENDING,
            'shipping_address' => fake()->address(),
            'contact_number' => '09'.fake()->numerify('#########'),
            'payment_method' => fake()->randomElement(Order::PAYMENT_METHODS),
        ];
    }

    /**
     * Indicate that the order has been completed and counts as a sale.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => Order::STATUS_COMPLETED,
        ]);
    }

    /**
     * Indicate that the order was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => Order::STATUS_CANCELLED,
        ]);
    }
}
