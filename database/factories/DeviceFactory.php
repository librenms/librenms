<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'type' => $this->faker->randomElement([
                'appliance',
                'camera',
                'collaboration',
                'encoder',
                'environment',
                'firewall',
                'loadbalancer',
                'management',
                'network',
                'power',
                'printer',
                'proxy',
                'sensor',
                'server',
                'storage',
                'timing',
                'wireless',
                'workstation',
            ]),
            'sysDescr' => $this->faker->text(64),
            'status' => $status = random_int(0, 1),
            'status_reason' => $status == 0 ? $this->faker->randomElement(['snmp', 'icmp']) : '', // allow invalid states?
        ];
    }
}
