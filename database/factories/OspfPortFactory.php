<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\OspfPort> */
class OspfPortFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomDigit(),
            'ospf_port_id' => $this->faker->randomDigit(),
            'ospfIfIpAddress' => $this->faker->ipv4(),
            'ospfAddressLessIf' => $this->faker->randomDigit(),
            'ospfIfAreaId' => '0.0.0.0',
        ];
    }
}
