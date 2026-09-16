<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Processor> */
class ProcessorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'processor_oid' => '.1.3.6.1.2.1.25.3.3.1.2.' . $this->faker->numberBetween(1, 8),
            'processor_index' => (string) $this->faker->unique()->numberBetween(1, 100),
            'processor_type' => $this->faker->randomElement(['hr', 'ucd', 'cpm']),
            'processor_usage' => $this->faker->numberBetween(0, 100),
            'processor_descr' => $this->faker->randomElement(['CPU 0', 'CPU 1', 'Memory Controller', 'Routing Processor']),
            'processor_precision' => 1,
            'processor_perc_warn' => 75,
        ];
    }
}
