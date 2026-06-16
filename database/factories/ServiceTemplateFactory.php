<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\ServiceTemplate> */
class ServiceTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'check' => $this->faker->randomElement(['icmp', 'http', 'tcp', 'dns']),
            'type' => 'static',
            'desc' => $this->faker->sentence(),
            'param' => '',
            'ip' => $this->faker->ipv4(),
            'disabled' => 0,
        ];
    }
}
