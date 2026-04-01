<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsLspPath> */
class MplsLspPathFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lsp_id' => $this->faker->numberBetween(1, 100),
            'path_oid' => $this->faker->numberBetween(1, 1000),
            'mplsLspPathRowStatus' => 'active',
            'mplsLspPathLastChange' => $this->faker->unixTime(),
            'mplsLspPathType' => 'primary',
            'mplsLspPathBandwidth' => 0,
            'mplsLspPathOperBandwidth' => 0,
            'mplsLspPathAdminState' => 'inService',
            'mplsLspPathOperState' => 'inService',
            'mplsLspPathState' => 'active',
            'mplsLspPathFailCode' => '',
            'mplsLspPathFailNodeAddr' => '',
            'mplsLspPathMetric' => 0,
        ];
    }
}
