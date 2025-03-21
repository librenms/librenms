<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\OspfNbr> */
class Ospfv3NbrFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomDigit(),
            'ospfv3_instance_id' => 0,
            'ospfv3NbrIpAddr' => $this->faker->ipv6(),
            'ospfv3NbrAddressLessIndex' => $this->faker->randomDigit(),
            'ospfv3NbrRtrId' => $this->faker->ipv4(),
            'ospfv3NbrOptions' => 0,
            'ospfv3NbrPriority' => 1,
            'ospfv3NbrEvents' => $this->faker->randomDigit(),
            'ospfv3NbrLsRetransQLen' => 0,
            'ospfv3NbmaNbrStatus' => 'active',
            'ospfv3NbmaNbrPermanence' => 'dynamic',
            'ospfv3NbrHelloSuppressed' => 'false',
        ];
    }
}
