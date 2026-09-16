<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\DiskIo> */
class DiskIoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'diskio_index' => $this->faker->numberBetween(1, 20),
            'diskio_descr' => $this->faker->randomElement(['sda', 'sdb', 'nvme0n1', 'dm-0']),
        ];
    }
}
