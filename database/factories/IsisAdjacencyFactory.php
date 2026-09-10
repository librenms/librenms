<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\IsisAdjacency> */
class IsisAdjacencyFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ifIndex' => $this->faker->numberBetween(1, 100),
            'index' => (string) $this->faker->numberBetween(1, 10),
            'isisISAdjState' => $this->faker->randomElement(['up', 'down', 'initializing']),
            'isisISAdjNeighSysType' => 'l1l2IntermediateSystem',
            'isisISAdjNeighSysID' => $this->faker->macAddress(),
            'isisISAdjNeighPriority' => (string) $this->faker->numberBetween(0, 127),
            'isisISAdjLastUpTime' => $this->faker->unixTime(),
            'isisISAdjAreaAddress' => '49.0001',
            'isisISAdjIPAddrType' => 'ipv4',
            'isisISAdjIPAddrAddress' => $this->faker->ipv4(),
            'isisCircAdminState' => 'on',
        ];
    }
}
