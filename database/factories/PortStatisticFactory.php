<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortStatistic> */
class PortStatisticFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
