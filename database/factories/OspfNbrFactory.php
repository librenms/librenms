<?php

namespace Database\Factories;

use App\Models\OspfNbr;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OspfNbr> */
class OspfNbrFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomDigit(),
            'ospfNbrIpAddr' => $this->faker->ipv4(),
            'ospfNbrAddressLessIndex' => $this->faker->randomDigit(),
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
