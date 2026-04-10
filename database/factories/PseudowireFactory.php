<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Pseudowire> */
class PseudowireFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'port_id' => $this->faker->numberBetween(1, 100),
            'peer_device_id' => $this->faker->numberBetween(1, 100),
            'peer_ldp_id' => $this->faker->numberBetween(1, 1000),
            'cpwVcID' => $this->faker->numberBetween(1, 10000),
            'cpwOid' => $this->faker->numberBetween(1, 1000),
            'pw_type' => $this->faker->randomElement(['ethernet', 'vlan', 'atmAal5Vcc']),
            'pw_psntype' => 'mpls',
            'pw_local_mtu' => 1500,
            'pw_peer_mtu' => 1500,
            'pw_descr' => 'PW-' . $this->faker->word(),
        ];
    }
}
