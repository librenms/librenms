<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortVdsl> */
class PortVdslFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->numberBetween(1, 100),
            'xdsl2LineStatusAttainableRateDs' => $this->faker->numberBetween(10000, 300000),
            'xdsl2LineStatusAttainableRateUs' => $this->faker->numberBetween(1000, 50000),
            'xdsl2ChStatusActDataRateXtur' => $this->faker->numberBetween(1000, 50000),
            'xdsl2ChStatusActDataRateXtuc' => $this->faker->numberBetween(10000, 300000),
            'xdsl2LineStatusActAtpDs' => $this->faker->randomFloat(2, 0, 30),
            'xdsl2LineStatusActAtpUs' => $this->faker->randomFloat(2, 0, 15),
        ];
    }
}
