<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Application> */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'app_type' => $this->faker->randomElement(['mysql', 'apache', 'nginx', 'bind', 'memcached', 'redis']),
            'app_instance' => '',
            'app_status' => '',
            'app_state' => 'UNKNOWN',
            'discovered' => 0,
        ];
    }
}
