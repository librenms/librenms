<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Process> */
class ProcessFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'pid' => $this->faker->unique()->numberBetween(1, 65535),
            'vsz' => $this->faker->numberBetween(1024, 1048576),
            'rss' => $this->faker->numberBetween(512, 524288),
            'cputime' => '00:01:23',
            'user' => $this->faker->userName(),
            'command' => '/usr/sbin/' . $this->faker->word(),
        ];
    }
}
