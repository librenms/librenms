<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PollerGroup> */
class PollerGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_name' => $this->faker->unique()->words(2, true),
            'descr' => $this->faker->sentence(),
        ];
    }
}
