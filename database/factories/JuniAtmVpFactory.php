<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\JuniAtmVp> */
class JuniAtmVpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'juniAtmVp_id' => 0,
            'vp_id' => $this->faker->numberBetween(0, 255),
            'vp_descr' => '',
        ];
    }
}
