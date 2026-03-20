<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AlertRule> */
class AlertRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'severity' => $this->faker->randomElement(['ok', 'warning', 'critical']),
            'extra' => '{}',
            'disabled' => 0,
            'query' => 'SELECT * FROM devices WHERE status = 1',
            'builder' => '{}',
            'proc' => null,
            'notes' => null,
            'invert_map' => 0,
        ];
    }
}
