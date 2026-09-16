<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\IpsecTunnel> */
class IpsecTunnelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'peer_port' => 500,
            'peer_addr' => $this->faker->unique()->ipv4(),
            'local_addr' => $this->faker->ipv4(),
            'local_port' => 500,
            'tunnel_name' => 'tunnel-' . $this->faker->word(),
            'tunnel_status' => $this->faker->randomElement(['active', 'destroy']),
        ];
    }
}
