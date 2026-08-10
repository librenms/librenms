<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'service_type' => $this->faker->randomElement(['icmp', 'http', 'tcp', 'dns', 'ssh']),
            'service_name' => $this->faker->words(2, true),
            'service_desc' => $this->faker->sentence(),
            'service_param' => '',
            'service_ip' => $this->faker->ipv4(),
            'service_ignore' => 0,
            'service_status' => 0,
            'service_changed' => 0,
            'service_message' => '',
            'service_disabled' => 0,
            'service_ds' => '',
            'service_template_id' => 0,
        ];
    }
}
