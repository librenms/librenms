<?php

namespace Database\Factories;

use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\AlertState;

/** @extends Factory<\App\Models\Alert> */
class AlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'rule_id' => AlertRule::factory(),
            'state' => AlertState::ACTIVE,
            'alerted' => 1,
            'open' => 1,
            'note' => '',
            'timestamp' => now(),
            'info' => '{}',
        ];
    }

    public function acknowledged(): static
    {
        return $this->state(['state' => AlertState::ACKNOWLEDGED]);
    }

    public function clear(): static
    {
        return $this->state(['state' => AlertState::CLEAR]);
    }
}
