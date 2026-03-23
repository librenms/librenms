<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ipv6Network> */
class Ipv6NetworkFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ipv6_network' => $this->faker->ipv6() . '/' . $this->faker->numberBetween(0, 128),
        ];
    }
}
