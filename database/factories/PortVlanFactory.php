<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortVlan> */
class PortVlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'port_id' => $this->faker->numberBetween(1, 100),
            'vlan' => $this->faker->numberBetween(1, 4094),
            'baseport' => $this->faker->numberBetween(1, 48),
            'priority' => 0,
            'state' => 'forwarding',
            'cost' => 0,
            'untagged' => 0,
        ];
    }
}
