<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\MplsLsp;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsLspPath> */
class MplsLspPathFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'lsp_id' => MplsLsp::factory(),
            'path_oid' => $this->faker->numberBetween(1, 10),
            'mplsLspPathRowStatus' => 'active',
            'mplsLspPathLastChange' => $this->faker->unixTime(),
            'mplsLspPathType' => 'primary',
            'mplsLspPathBandwidth' => 1000000,
            'mplsLspPathOperBandwidth' => 1000000,
            'mplsLspPathAdminState' => 'inService',
            'mplsLspPathOperState' => 'inService',
            'mplsLspPathState' => 'active',
            'mplsLspPathFailCode' => 'noError',
            'mplsLspPathFailNodeAddr' => '',
            'mplsLspPathMetric' => 10,
            'mplsLspPathOperMetric' => 10,
            'mplsLspPathTimeUp' => 864000,
            'mplsLspPathTimeDown' => 0,
            'mplsLspPathTransitionCount' => 0,
            'mplsLspPathTunnelARHopListIndex' => 1,
            'mplsLspPathTunnelCHopListIndex' => 1,
        ];
    }

    public function primary(): static
    {
        return $this->state([
            'mplsLspPathType' => 'primary',
            'mplsLspPathState' => 'active',
        ]);
    }

    public function standby(): static
    {
        return $this->state([
            'mplsLspPathType' => 'standby',
            'mplsLspPathState' => 'active',
        ]);
    }

    public function secondary(): static
    {
        return $this->state([
            'mplsLspPathType' => 'secondary',
        ]);
    }

    public function inService(): static
    {
        return $this->state([
            'mplsLspPathAdminState' => 'inService',
            'mplsLspPathOperState' => 'inService',
        ]);
    }

    public function outOfService(): static
    {
        return $this->state([
            'mplsLspPathAdminState' => 'outOfService',
            'mplsLspPathOperState' => 'outOfService',
        ]);
    }
}
