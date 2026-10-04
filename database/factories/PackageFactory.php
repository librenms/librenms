<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Package> */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'name' => $this->faker->word(),
            'manager' => $this->faker->randomElement(['deb', 'rpm', 'apk', 'pacman']),
            'status' => 1,
            'version' => $this->faker->numerify('#.#.#'),
            'build' => $this->faker->numerify('##'),
            'arch' => $this->faker->randomElement(['amd64', 'x86_64', 'arm64']),
            'size' => $this->faker->numberBetween(10000, 50000000),
        ];
    }
}
