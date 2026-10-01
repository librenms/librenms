<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AuthLog> */
class AuthLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'datetime' => $this->faker->dateTimeThisMonth(),
            'user' => $this->faker->userName(),
            'address' => $this->faker->ipv4(),
            'result' => $this->faker->randomElement(['Logged In', 'Authentication Failure']),
        ];
    }
}
