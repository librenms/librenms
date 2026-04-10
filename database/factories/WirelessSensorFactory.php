<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\WirelessSensor> */
class WirelessSensorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sensor_class' => $this->faker->randomElement(['ap-count', 'clients', 'rssi', 'snr', 'power', 'rate', 'frequency']),
            'sensor_type' => 'test',
            'sensor_descr' => 'Wireless ' . $this->faker->word(),
            'sensor_oids' => '[]',
            'sensor_alert' => 1,
            'sensor_custom' => 'No',
        ];
    }
}
