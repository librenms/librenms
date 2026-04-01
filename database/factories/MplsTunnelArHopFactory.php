<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsTunnelArHop> */
class MplsTunnelArHopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mplsTunnelARHopListIndex' => $this->faker->numberBetween(1, 100),
            'mplsTunnelARHopIndex' => $this->faker->numberBetween(1, 10),
            'lsp_path_id' => $this->faker->numberBetween(1, 100),
            'mplsTunnelARHopAddrType' => 'ipV4',
            'mplsTunnelARHopIpv4Addr' => $this->faker->ipv4(),
            'mplsTunnelARHopStrictOrLoose' => 'strict',
        ];
    }
}
