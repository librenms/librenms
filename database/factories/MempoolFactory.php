<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Mempool> */
class MempoolFactory extends Factory
{
    public function definition(): array
    {
        $total = $this->faker->numberBetween(1073741824, 17179869184);
        $used = (int) ($total * $this->faker->numberBetween(10, 90) / 100);

        return [
            'device_id' => Device::factory(),
            'mempool_index' => (string) $this->faker->unique()->numberBetween(1, 100),
            'mempool_type' => $this->faker->randomElement(['hrstorage', 'ucd', 'cemp']),
            'mempool_class' => $this->faker->randomElement(['system', 'buffers', 'cached']),
            'mempool_descr' => $this->faker->randomElement(['Physical Memory', 'Swap Space', 'DRAM']),
            'mempool_precision' => 1,
            'mempool_perc' => (int) ($used / $total * 100),
            'mempool_used' => $used,
            'mempool_free' => $total - $used,
            'mempool_total' => $total,
            'mempool_perc_warn' => 90,
        ];
    }
}
