<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ospfv3Port> */
class Ospfv3PortFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ospfv3_instance_id' => $this->faker->numberBetween(1, 100),
            'ospfv3IfIndex' => $this->faker->numberBetween(1, 100),
            'ospfv3IfInstId' => 0,
            'ospfv3IfAreaId' => 0,
            'ospfv3IfType' => 'broadcast',
            'ospfv3IfAdminStatus' => 'enabled',
            'ospfv3IfRtrPriority' => 1,
            'ospfv3IfTransitDelay' => 1,
            'ospfv3IfRetransInterval' => 5,
            'ospfv3IfHelloInterval' => 10,
            'ospfv3IfRtrDeadInterval' => 40,
            'ospfv3IfPollInterval' => 120,
            'ospfv3IfState' => $this->faker->randomElement(['dr', 'bdr', 'drOther', 'pointToPoint']),
            'ospfv3IfDesignatedRouter' => $this->faker->ipv4(),
            'ospfv3IfBackupDesignatedRouter' => $this->faker->ipv4(),
            'ospfv3IfEvents' => $this->faker->numberBetween(0, 100),
            'ospfv3IfDemand' => 'false',
            'ospfv3IfMetricValue' => $this->faker->numberBetween(1, 65535),
        ];
    }
}
