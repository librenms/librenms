<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\MplsLspPath;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsTunnelArHop> */
class MplsTunnelArHopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'lsp_path_id' => MplsLspPath::factory(),
            'mplsTunnelARHopListIndex' => 1,
            'mplsTunnelARHopIndex' => 1,
            'mplsTunnelARHopAddrType' => 'ipV4',
            'mplsTunnelARHopIpv4Addr' => $this->faker->ipv4(),
            'mplsTunnelARHopStrictOrLoose' => 'strict',
            'localProtected' => 'false',
            'linkProtectionInUse' => 'false',
            'bandwidthProtected' => 'false',
            'nextNodeProtected' => 'false',
        ];
    }
}
