<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' API',
            'base_url' => $this->faker->url(),
            'health_endpoint' => '/health',
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'timeout_seconds' => $this->faker->numberBetween(10, 60),
            'retry_count' => $this->faker->numberBetween(1, 5),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'is_backup' => $this->faker->boolean(20), // 20% chance of being backup
            'priority' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function backup(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_backup' => true,
            'status' => 'active',
            'priority' => $this->faker->numberBetween(1, 5),
        ]);
    }
}