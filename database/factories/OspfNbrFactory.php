<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\OspfNbr> */
class OspfNbrFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ospf_nbr_id' => $this->faker->unique()->ipv4(),
            'ospfNbrIpAddr' => $this->faker->ipv4(),
            'ospfNbrAddressLessIndex' => $this->faker->randomDigit(),
            'ospfNbrState' => $this->faker->randomElement(['full', 'init', 'twoWay']),
            'ospfNbrRtrId' => $this->faker->ipv4(),
            'ospfNbrOptions' => 0,
            'ospfNbrPriority' => 1,
            'ospfNbrEvents' => $this->faker->randomDigit(),
            'ospfNbrLsRetransQLen' => 0,
            'ospfNbmaNbrStatus' => 'active',
            'ospfNbmaNbrPermanence' => 'dynamic',
            'ospfNbrHelloSuppressed' => 'false',
        ];
    }
}
