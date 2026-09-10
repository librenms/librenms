<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Util\IPv6;

/** @extends Factory<\App\Models\Ipv6Network> */
class Ipv6NetworkFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $prefix = $this->faker->numberBetween(0, 128);
        $ip = new IPv6($this->faker->ipv6() . '/' . $prefix);

        return [
            'ipv6_network' => $ip->getNetworkAddress() . '/' . $prefix,
        ];
    }
}
