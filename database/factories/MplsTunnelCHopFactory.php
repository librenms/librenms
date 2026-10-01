<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\MplsLspPath;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsTunnelCHop> */
class MplsTunnelCHopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'lsp_path_id' => MplsLspPath::factory(),
            'mplsTunnelCHopListIndex' => 1,
            'mplsTunnelCHopIndex' => 1,
            'mplsTunnelCHopAddrType' => 'ipV4',
            'mplsTunnelCHopIpv4Addr' => $this->faker->ipv4(),
            'mplsTunnelCHopStrictOrLoose' => 'strict',
        ];
    }
}
