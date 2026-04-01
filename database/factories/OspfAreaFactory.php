<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\OspfArea> */
class OspfAreaFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ospfAreaId' => $this->faker->ipv4(),
            'ospfImportAsExtern' => 'isExternDoNotImport',
            'ospfSpfRuns' => $this->faker->numberBetween(0, 1000),
            'ospfAreaBdrRtrCount' => $this->faker->numberBetween(0, 10),
            'ospfAsBdrRtrCount' => $this->faker->numberBetween(0, 10),
            'ospfAreaLsaCount' => $this->faker->numberBetween(0, 1000),
            'ospfAreaLsaCksumSum' => $this->faker->numberBetween(0, 100000),
            'ospfAreaSummary' => 'noAreaSummary',
            'ospfAreaStatus' => 'active',
        ];
    }
}
