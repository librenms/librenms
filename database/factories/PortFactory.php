<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Port> */
class PortFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ifIndex' => $this->faker->unique()->numberBetween(),
            'ifName' => $this->faker->text(20),
            'ifDescr' => $this->faker->text(255),
            'ifLastChange' => $this->faker->unixTime(),
        ];
    }
}
