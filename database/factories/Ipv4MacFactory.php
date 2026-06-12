<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ipv4Mac> */
class Ipv4MacFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->numberBetween(1, 100),
            'mac_address' => $this->faker->macAddress(),
            'ipv4_address' => $this->faker->ipv4(),
        ];
    }
}
