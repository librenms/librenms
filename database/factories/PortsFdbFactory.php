<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortsFdb> */
class PortsFdbFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->numberBetween(1, 100),
            'mac_address' => $this->faker->macAddress(),
            'vlan_id' => $this->faker->numberBetween(1, 4094),
            'device_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
