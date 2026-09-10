<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\VrfLite> */
class VrfLiteFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'context_name' => $this->faker->word(),
            'intance_name' => $this->faker->word(),
            'vrf_name' => 'VRF_' . $this->faker->word(),
        ];
    }
}
