<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Sla> */
class SlaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sla_nr' => $this->faker->unique()->numberBetween(1, 10000),
            'owner' => $this->faker->word(),
            'tag' => $this->faker->word(),
            'rtt_type' => $this->faker->randomElement(['icmpEcho', 'udpEcho', 'tcpConnect', 'jitter']),
            'rtt' => $this->faker->randomFloat(2, 1, 500),
            'status' => 1,
            'opstatus' => 1,
        ];
    }
}
