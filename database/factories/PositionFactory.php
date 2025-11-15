<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'election_id' => \App\Models\Election::factory(),
            'name' => fake()->randomElement(['President', 'Vice President', 'Secretary', 'Treasurer']),
            'description' => fake()->sentence(),
            'max_votes' => 1,
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }
}
