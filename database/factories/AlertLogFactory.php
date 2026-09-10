<?php

namespace Database\Factories;

use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AlertLog> */
class AlertLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'rule_id' => AlertRule::factory(),
            'state' => $this->faker->randomElement([0, 1, 2]),
            'details' => '{}',
            'time_logged' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
