<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Sensor> */
class SensorFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $sensor_class = ['airflow', 'ber', 'charge', 'chromatic_dispersion', 'cooling', 'count', 'current', 'dbm', 'delay', 'eer', 'fanspeed', 'frequency', 'humidity', 'load', 'loss', 'power', 'power_consumed', 'power_factor', 'pressure', 'quality_factor', 'runtime', 'signal', 'snr', 'state', 'temperature', 'voltage', 'waterflow'];
        $sensor_oid = '.1.3.6.1.4.1.4115.1.4.3.3.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10);

        return [
            'sensor_index' => $this->faker->randomDigit(),
            'sensor_class' => $this->faker->randomElement($sensor_class),
            'sensor_current' => $this->faker->randomDigit(),
            'sensor_oid' => $sensor_oid,
        ];
    }
}
