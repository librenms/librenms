<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\DeviceType;

/** @extends Factory<\App\Models\Device> */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'hostname' => $this->faker->domainWord() . '-' . $this->faker->domainWord() . '-' . $this->faker->domainWord() . '.' . $this->faker->domainName(),
            'ip' => $this->faker->randomElement([$this->faker->ipv4(), $this->faker->ipv6()]),
            'type' => $this->faker->randomElement(array_column(DeviceType::cases(), 'value')),
            'sysDescr' => $this->faker->text(64),
            'status' => $status = random_int(0, 1),
            'status_reason' => $status == 0 ? $this->faker->randomElement(['snmp', 'icmp']) : '', // allow invalid states?
        ];
    }
}
