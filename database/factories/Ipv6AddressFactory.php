<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ipv6Address> */
class Ipv6AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ipv6_address' => $this->faker->ipv6(),
            'ipv6_compressed' => $this->faker->ipv6(),
            'ipv6_prefixlen' => 64,
            'ipv6_origin' => 'manual',
            'ipv6_network_id' => 0,
        ];
    }
}
