<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Vrf> */
class VrfFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'vrf_oid' => $this->faker->numerify('1.3.6.1.4.1.####.#.#'),
            'vrf_name' => 'VRF_' . $this->faker->word(),
            'mplsVpnVrfDescription' => $this->faker->sentence(),
            'mplsVpnVrfRouteDistinguisher' => $this->faker->numerify('####:####'),
        ];
    }
}
