<?php

namespace Database\Factories;

use App\Models\PollerCluster;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PollerClusterStat> */
class PollerClusterStatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_poller' => PollerCluster::factory(),
            'poller_type' => $this->faker->unique()->randomElement(['poller', 'discovery', 'services', 'alerting', 'billing', 'ping']),
            'depth' => $this->faker->numberBetween(0, 50),
            'devices' => $this->faker->numberBetween(1, 100),
            'worker_seconds' => $this->faker->randomFloat(2, 0, 300),
            'workers' => $this->faker->numberBetween(1, 16),
            'frequency' => 300,
        ];
    }
}
