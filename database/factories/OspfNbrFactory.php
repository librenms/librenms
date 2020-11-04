<?php

namespace Database\Factories;

use App\Models\OspfNbr;
use Illuminate\Database\Eloquent\Factories\Factory;

class OspfNbrFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OspfNbr::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->randomDigit,
            'ospfNbrIpAddr' => $this->faker->ipv4,
            'ospfNbrAddressLessIndex' => $this->faker->randomDigit,
            'ospfNbrRtrId' => $this->faker->ipv4,
            'ospfNbrOptions' => 0,
            'ospfNbrPriority' => 1,
            'ospfNbrEvents' => $this->faker->randomDigit,
            'ospfNbrLsRetransQLen' => 0,
            'ospfNbmaNbrStatus' => 'active',
            'ospfNbmaNbrPermanence' => 'dynamic',
            'ospfNbrHelloSuppressed' => 'false',
        ];
    }
}
