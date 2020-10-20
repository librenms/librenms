<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'hostname' => $this->faker->domainWord . '-' . $this->faker->domainWord . '-' . $this->faker->domainWord . '.' . $this->faker->domainName,
            'ip' => $this->faker->randomElement([$this->faker->ipv4, $this->faker->ipv6]),
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
            'status' => $status = random_int(0, 1),
            'status_reason' => $status == 0 ? $this->faker->randomElement(['snmp', 'icmp']) : '', // allow invalid states?
        ];
    }
}
