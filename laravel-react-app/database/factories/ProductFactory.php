<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'product_name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 50, 2000),
            'available_quantity' => fake()->numberBetween(20, 100),
            'reorder_level' => 10,
            'image' => null,
            'status' => Product::STATUS_ACTIVE,
        ];
    }

    /**
     * Indicate that the product is hidden from customers.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_INACTIVE,
        ]);
    }

    /**
     * Indicate that the product's stock is at or below its reorder level.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_quantity' => 3,
            'reorder_level' => 10,
        ]);
    }
}
