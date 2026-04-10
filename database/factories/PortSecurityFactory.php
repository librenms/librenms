<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortSecurity> */
class PortSecurityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->unique()->numberBetween(1, 10000),
            'device_id' => $this->faker->numberBetween(1, 100),
            'port_security_enable' => 'true',
            'status' => 'secureup',
            'max_addresses' => 1,
            'address_count' => 1,
            'violation_action' => 'shutdown',
            'violation_count' => 0,
            'last_mac_address' => $this->faker->macAddress(),
        ];
    }
}
