<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortsNac> */
class PortsNacFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auth_id' => (string) $this->faker->numberBetween(1, 10000),
            'device_id' => $this->faker->numberBetween(1, 100),
            'port_id' => $this->faker->numberBetween(1, 100),
            'domain' => $this->faker->domainWord(),
            'username' => $this->faker->userName(),
            'mac_address' => $this->faker->macAddress(),
            'ip_address' => $this->faker->ipv4(),
            'host_mode' => 'multi-auth',
            'authz_status' => $this->faker->randomElement(['authorizationSuccess', 'authorizationFailed']),
            'authz_by' => 'radius',
            'authc_status' => 'success',
            'method' => 'dot1x',
            'timeout' => 'N/A',
        ];
    }
}
