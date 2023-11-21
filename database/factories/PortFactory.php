<?php

namespace Database\Factories;

use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Port> */
class PortFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
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
