<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ipv6Nd> */
class Ipv6NdFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->numberBetween(1, 100),
            'device_id' => $this->faker->numberBetween(1, 100),
            'mac_address' => $this->faker->macAddress(),
            'ipv6_address' => $this->faker->ipv6(),
        ];
    }
}
