<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\MplsSdp;
use App\Models\MplsService;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsSdpBind> */
class MplsSdpBindFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'sdp_id' => MplsSdp::factory(),
            'svc_id' => MplsService::factory(),
            'sdp_oid' => fn (array $attributes) => MplsSdp::find($attributes['sdp_id'])->sdp_oid ?? $this->faker->numberBetween(1, 1000),
            'svc_oid' => fn (array $attributes) => MplsService::find($attributes['svc_id'])->svc_oid ?? $this->faker->numberBetween(1, 1000),
            'sdpBindRowStatus' => 'active',
            'sdpBindAdminStatus' => 'up',
            'sdpBindOperStatus' => 'up',
            'sdpBindLastMgmtChange' => $this->faker->unixTime(),
            'sdpBindLastStatusChange' => $this->faker->unixTime(),
            'sdpBindType' => 'spoke',
            'sdpBindVcType' => 'ether',
            'sdpBindBaseStatsIngFwdPackets' => 1000,
            'sdpBindBaseStatsIngFwdOctets' => 64000,
            'sdpBindBaseStatsEgrFwdPackets' => 1000,
            'sdpBindBaseStatsEgrFwdOctets' => 64000,
        ];
    }

    public function spoke(): static
    {
        return $this->state(['sdpBindType' => 'spoke']);
    }

    public function mesh(): static
    {
        return $this->state(['sdpBindType' => 'mesh']);
    }

    public function up(): static
    {
        return $this->state([
            'sdpBindAdminStatus' => 'up',
            'sdpBindOperStatus' => 'up',
        ]);
    }

    public function down(): static
    {
        return $this->state([
            'sdpBindAdminStatus' => 'down',
            'sdpBindOperStatus' => 'down',
        ]);
    }
}
