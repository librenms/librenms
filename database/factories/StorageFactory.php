<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Storage> */
class StorageFactory extends Factory
{
    public function definition(): array
    {
        $size = $this->faker->numberBetween(10737418240, 1099511627776);
        $used = (int) ($size * $this->faker->numberBetween(10, 90) / 100);

        return [
            'device_id' => Device::factory(),
            'storage_index' => (string) $this->faker->unique()->numberBetween(1, 100),
            'type' => 'hrstorage',
            'storage_type' => $this->faker->randomElement(['hrStorageFixedDisk', 'hrStorageVirtualMemory', 'dsk']),
            'storage_descr' => $this->faker->randomElement(['/', '/home', '/var', '/tmp', 'C:\\']),
            'storage_size' => $size,
            'storage_units' => 4096,
            'storage_used' => $used,
            'storage_free' => $size - $used,
            'storage_perc' => (int) ($used / $size * 100),
            'storage_perc_warn' => 80,
        ];
    }
}
