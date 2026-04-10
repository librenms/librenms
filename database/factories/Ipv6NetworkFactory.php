<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ipv6Network> */
class Ipv6NetworkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ipv6_network' => $this->faker->ipv6() . '/64',
        ];
    }
}
