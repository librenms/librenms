<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ipv4Network> */
class Ipv4NetworkFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ipv4_network' => $this->faker->ipv4() . '/' . $this->faker->numberBetween(0, 32),
        ];
    }
}
