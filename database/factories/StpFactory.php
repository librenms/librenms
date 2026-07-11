<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Stp> */
class StpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rootBridge' => $this->faker->boolean(),
            'bridgeAddress' => $this->faker->macAddress(),
            'protocolSpecification' => 'ieee8021d',
            'priority' => 32768,
            'timeSinceTopologyChange' => (string) $this->faker->numberBetween(0, 1000000),
            'topChanges' => $this->faker->numberBetween(0, 100),
            'designatedRoot' => $this->faker->macAddress(),
            'rootCost' => 0,
            'maxAge' => 2000,
            'helloTime' => 200,
            'holdTime' => 100,
            'forwardDelay' => 1500,
            'bridgeMaxAge' => 2000,
            'bridgeHelloTime' => 200,
            'bridgeForwardDelay' => 1500,
        ];
    }
}
