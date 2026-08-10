<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\WirelessSensorType;

/** @extends Factory<\App\Models\WirelessSensor> */
class WirelessSensorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sensor_class' => $this->faker->randomElement(WirelessSensorType::values()),
            'sensor_index' => (string) $this->faker->randomDigit(),
            'sensor_type' => 'generic',
            'sensor_current' => $this->faker->randomDigit(),
            'sensor_oids' => ['value' => '.1.3.6.1.4.1.17713.21.1.2.30.1.4.1'],
        ];
    }
}
