<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ospfv3Nbr> */
class Ospfv3NbrFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ospfv3_instance_id' => $this->faker->numberBetween(1, 100),
            'router_id' => $this->faker->unique()->ipv4(),
            'ospfv3NbrIfIndex' => $this->faker->numberBetween(1, 100),
            'ospfv3NbrIfInstId' => 0,
            'ospfv3NbrRtrId' => $this->faker->numberBetween(1, 4294967295),
            'ospfv3NbrAddressType' => 'ipv6',
            'ospfv3NbrAddress' => $this->faker->ipv6(),
            'ospfv3NbrOptions' => 0,
            'ospfv3NbrPriority' => 1,
            'ospfv3NbrState' => $this->faker->randomElement(['full', 'init', 'twoWay']),
            'ospfv3NbrEvents' => $this->faker->numberBetween(0, 100),
            'ospfv3NbrLsRetransQLen' => 0,
            'ospfv3NbrHelloSuppressed' => 'false',
            'ospfv3NbrIfId' => $this->faker->numberBetween(1, 100),
            'ospfv3NbrRestartHelperStatus' => 'notHelping',
            'ospfv3NbrRestartHelperAge' => 0,
            'ospfv3NbrRestartHelperExitReason' => 'none',
        ];
    }
}
