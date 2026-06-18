<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsLsp> */
class MplsLspFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vrf_oid' => $this->faker->numberBetween(1, 100),
            'lsp_oid' => $this->faker->numberBetween(1, 1000),
            'mplsLspRowStatus' => 'active',
            'mplsLspName' => 'LSP_' . $this->faker->word(),
            'mplsLspAdminState' => 'inService',
            'mplsLspOperState' => 'inService',
            'mplsLspFromAddr' => $this->faker->ipv4(),
            'mplsLspToAddr' => $this->faker->ipv4(),
            'mplsLspType' => 'dynamic',
            'mplsLspFastReroute' => 'true',
        ];
    }
}
