<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'usn' => fake()->unique()->numerify('####-###-BS'),
            'email' => fake()->unique()->safeEmail(),
            'strand' => fake()->randomElement(['STEM', 'ABM', 'HUMSS', 'GAS']),
            'year' => fake()->randomElement([1, 2, 3, 4, 11, 12]),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'user_type' => 'student',
            'has_voted' => false,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }


}
