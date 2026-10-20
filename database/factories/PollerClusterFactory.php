<?php

namespace Database\Factories;

use App\Horizon\HorizonNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PollerCluster> */
class PollerClusterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'node_id' => $this->faker->unique()->uuid(),
            'poller_name' => $this->faker->unique()->domainWord() . '-poller',
            'poller_version' => 'librenms-service',
            'poller_groups' => '0',
            'last_report' => $this->faker->dateTimeThisMonth(),
            'master' => 0,
        ];
    }

    public function horizon(): static
    {
        return $this->state(['poller_version' => HorizonNode::VERSION]);
    }
}
