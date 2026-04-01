<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsTunnelCHop> */
class MplsTunnelCHopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mplsTunnelCHopListIndex' => $this->faker->numberBetween(1, 100),
            'mplsTunnelCHopIndex' => $this->faker->numberBetween(1, 10),
            'lsp_path_id' => $this->faker->numberBetween(1, 100),
            'mplsTunnelCHopAddrType' => 'ipV4',
            'mplsTunnelCHopIpv4Addr' => $this->faker->ipv4(),
            'mplsTunnelCHopStrictOrLoose' => 'strict',
        ];
    }
}
