<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsSdp> */
class MplsSdpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sdp_oid' => $this->faker->numberBetween(1, 1000),
            'sdpRowStatus' => 'active',
            'sdpDelivery' => 'mpls',
            'sdpDescription' => 'SDP_' . $this->faker->word(),
            'sdpAdminStatus' => 'up',
            'sdpOperStatus' => 'up',
            'sdpFarEndInetAddressType' => 'ipv4',
            'sdpFarEndInetAddress' => $this->faker->ipv4(),
        ];
    }
}
