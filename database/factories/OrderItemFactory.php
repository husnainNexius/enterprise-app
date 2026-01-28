<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'product_id' => \App\Models\Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->randomFloat(10, 1000),
            'total_price' => $this->faker->randomFloat(10, 10000),
            'product_snapshot' => [
                'name' => $this->faker->words(3),
                'description' => $this->faker->sentence,
                'price' => $this->faker->randomFloat(10, 1000),
                'category' => $this->faker->word,
            ],
        ];
    }
}
