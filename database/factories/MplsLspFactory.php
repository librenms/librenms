<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsLsp> */
class MplsLspFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'vrf_oid' => 0,
            'lsp_oid' => $this->faker->numberBetween(1, 1000),
            'mplsLspRowStatus' => 'active',
            'mplsLspLastChange' => $this->faker->unixTime(),
            'mplsLspName' => 'LSP_' . $this->faker->word(),
            'mplsLspAdminState' => 'inService',
            'mplsLspOperState' => 'inService',
            'mplsLspFromAddr' => $this->faker->ipv4(),
            'mplsLspToAddr' => $this->faker->ipv4(),
            'mplsLspType' => 'dynamic',
            'mplsLspFastReroute' => 'true',
            'mplsLspAge' => 864000,
            'mplsLspTimeUp' => 864000,
            'mplsLspTimeDown' => null,
            'mplsLspPrimaryTimeUp' => 864000,
            'mplsLspTransitions' => 1,
            'mplsLspLastTransition' => $this->faker->unixTime(),
            'mplsLspConfiguredPaths' => 1,
            'mplsLspStandbyPaths' => 0,
            'mplsLspOperationalPaths' => 1,
        ];
    }

    public function inService(): static
    {
        return $this->state([
            'mplsLspAdminState' => 'inService',
            'mplsLspOperState' => 'inService',
        ]);
    }

    public function outOfService(): static
    {
        return $this->state([
            'mplsLspAdminState' => 'outOfService',
            'mplsLspOperState' => 'outOfService',
        ]);
    }

    public function static(): static
    {
        return $this->state(['mplsLspType' => 'static']);
    }

    public function dynamic(): static
    {
        return $this->state(['mplsLspType' => 'dynamic']);
    }

    public function fastReroute(bool $enabled = true): static
    {
        return $this->state(['mplsLspFastReroute' => $enabled ? 'true' : 'false']);
    }
}
