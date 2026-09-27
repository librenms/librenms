<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\TnmsneInfo> */
class TnmsneInfoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'neID' => $this->faker->unique()->numberBetween(1, 1000),
            'neType' => 'NE-8000',
            'neName' => 'Coriant-Node-' . $this->faker->word(),
            'neLocation' => 'Rack-' . $this->faker->numberBetween(1, 20),
            'neAlarm' => 'none',
            'neOpMode' => 'normal',
            'neOpState' => 'inService',
        ];
    }
}
