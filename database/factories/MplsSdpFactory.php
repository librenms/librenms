<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsSdp> */
class MplsSdpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'sdp_oid' => $this->faker->numberBetween(1, 1000),
            'sdpRowStatus' => 'active',
            'sdpDelivery' => 'mpls',
            'sdpDescription' => 'SDP_' . $this->faker->word(),
            'sdpAdminStatus' => 'up',
            'sdpOperStatus' => 'up',
            'sdpAdminPathMtu' => 1514,
            'sdpOperPathMtu' => 1514,
            'sdpLastMgmtChange' => $this->faker->unixTime(),
            'sdpLastStatusChange' => $this->faker->unixTime(),
            'sdpActiveLspType' => 'rsvp',
            'sdpFarEndInetAddressType' => 'ipv4',
            'sdpFarEndInetAddress' => $this->faker->ipv4(),
        ];
    }

    public function up(): static
    {
        return $this->state([
            'sdpAdminStatus' => 'up',
            'sdpOperStatus' => 'up',
        ]);
    }

    public function down(): static
    {
        return $this->state([
            'sdpAdminStatus' => 'down',
            'sdpOperStatus' => 'down',
        ]);
    }

    public function mpls(): static
    {
        return $this->state(['sdpDelivery' => 'mpls']);
    }

    public function gre(): static
    {
        return $this->state(['sdpDelivery' => 'gre']);
    }
}
