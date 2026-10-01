<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortStp> */
class PortStpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'port_id' => $this->faker->numberBetween(1, 100),
            'vlan' => $this->faker->numberBetween(1, 4094),
            'port_index' => $this->faker->numberBetween(1, 48),
            'priority' => 128,
            'state' => $this->faker->randomElement(['forwarding', 'blocking', 'listening', 'learning', 'disabled']),
            'enable' => 'enabled',
            'pathCost' => $this->faker->numberBetween(1, 200000),
            'designatedRoot' => $this->faker->macAddress(),
            'designatedCost' => 0,
            'designatedBridge' => $this->faker->macAddress(),
            'designatedPort' => $this->faker->numberBetween(1, 48),
            'forwardTransitions' => $this->faker->numberBetween(0, 100),
        ];
    }
}
