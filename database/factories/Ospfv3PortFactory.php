<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\OspfPort> */
class Ospfv3PortFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomDigit(),
            'ospfv3_instance_id' => 0,
            'ospfv3_port_id' => $this->faker->randomDigit(),
            'ospfv3IfIpAddress' => $this->faker->ipv6(),
            'ospfv3AddressLessIf' => $this->faker->randomDigit(),
            'ospfv3IfAreaId' => '0.0.0.0',
        ];
    }
}
