<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Link> */
class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'local_device_id' => $this->faker->numberBetween(1, 100),
            'remote_device_id' => $this->faker->numberBetween(1, 100),
            'active' => 1,
            'protocol' => $this->faker->randomElement(['lldp', 'cdp']),
            'remote_hostname' => $this->faker->domainName(),
            'remote_port' => 'GigabitEthernet0/' . $this->faker->numberBetween(1, 48),
            'remote_version' => $this->faker->sentence(3),
        ];
    }
}
