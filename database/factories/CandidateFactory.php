<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => \App\Models\Position::factory(),
            'party_id' => \App\Models\Party::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'course' => fake()->randomElement(['BSIT', 'BSCS', 'BSCPE']),
            'year_level' => fake()->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
        ];
    }
}
