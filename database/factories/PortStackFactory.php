<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortStack> */
class PortStackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'high_ifIndex' => $this->faker->numberBetween(1, 100),
            'low_ifIndex' => $this->faker->numberBetween(1, 100),
            'ifStackStatus' => 'active',
        ];
    }
}
