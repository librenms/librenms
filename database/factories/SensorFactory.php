<?php

namespace Database\Factories;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\Sensor as SensorEnum;

/** @extends Factory<Sensor> */
class SensorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sensor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sensor_class = array_keys(SensorEnum::CLASSES);
        $sensor_oid = '.1.3.6.1.4.1.4115.1.4.3.3.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10) . '.' . $this->faker->numberBetween(0, 10);

        return [
            'sensor_index' => $this->faker->randomDigit(),
            'sensor_class' => $this->faker->randomElement($sensor_class),
            'sensor_current' => $this->faker->randomDigit(),
            'sensor_oid' => $sensor_oid,
        ];
    }
}
