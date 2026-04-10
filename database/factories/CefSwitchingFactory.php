<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\CefSwitching> */
class CefSwitchingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entPhysicalIndex' => $this->faker->numberBetween(1, 100),
            'afi' => $this->faker->randomElement(['ipv4', 'ipv6']),
            'cef_index' => $this->faker->numberBetween(1, 10),
            'cef_path' => $this->faker->randomElement(['receive', 'punt', 'drop']),
            'drop' => $this->faker->numberBetween(0, 1000),
            'punt' => $this->faker->numberBetween(0, 1000),
            'punt2host' => $this->faker->numberBetween(0, 1000),
            'drop_prev' => 0,
            'punt_prev' => 0,
            'punt2host_prev' => 0,
            'updated' => time(),
            'updated_prev' => time() - 300,
        ];
    }
}
