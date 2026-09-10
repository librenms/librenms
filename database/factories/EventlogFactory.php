<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Eventlog> */
class EventlogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'datetime' => $this->faker->dateTimeThisMonth(),
            'message' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['interface', 'system', 'reboot', 'bgp']),
            'reference' => null,
            'username' => '',
            'severity' => $this->faker->randomElement([1, 2, 3, 4, 5]),
        ];
    }
}
