<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
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
            'customer_id' => \App\Models\User::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']),
            'total_amount' => $this->faker->randomFloat(100, 5000),
            'tax_amount' => $this->faker->randomFloat(10, 500),
            'shipping_amount' => $this->faker->randomFloat(0, 50),
            'discount_amount' => $this->faker->randomFloat(0, 100),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'paypal', 'stripe']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'shipping_address' => [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'phone' => $this->faker->phoneNumber,
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => $this->faker->country,
            ],
            'billing_address' => [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'phone' => $this->faker->phoneNumber,
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => $this->faker->country,
            ],
            'notes' => $this->faker->text,
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'shipped_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'delivered_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
