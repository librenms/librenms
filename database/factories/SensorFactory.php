<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\Sensor;

/** @extends Factory<\App\Models\Sensor> */
class SensorFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $sensor_oid = '.1.3.6.1.4.1.4115.1.4.3.3.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10);

        return [
            'sensor_index' => $this->faker->randomDigit(),
            'sensor_class' => $this->faker->randomElement(Sensor::values()),
            'sensor_current' => $this->faker->randomDigit(),
            'sensor_oid' => $sensor_oid,
        ];
    }
}
