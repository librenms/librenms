<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ospfv3Area> */
class Ospfv3AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ospfv3_instance_id' => $this->faker->numberBetween(1, 100),
            'ospfv3AreaId' => $this->faker->numberBetween(0, 255),
            'ospfv3AreaImportAsExtern' => 'isExternDoNotImport',
            'ospfv3AreaSpfRuns' => $this->faker->numberBetween(0, 1000),
            'ospfv3AreaBdrRtrCount' => $this->faker->numberBetween(0, 10),
            'ospfv3AreaAsBdrRtrCount' => $this->faker->numberBetween(0, 10),
            'ospfv3AreaScopeLsaCount' => $this->faker->numberBetween(0, 1000),
            'ospfv3AreaScopeLsaCksumSum' => $this->faker->numberBetween(0, 100000),
            'ospfv3AreaSummary' => 'noAreaSummary',
            'ospfv3AreaStubMetric' => 0,
            'ospfv3AreaStubMetricType' => 'ospfV3Metric',
            'ospfv3AreaNssaTranslatorRole' => 'always',
            'ospfv3AreaNssaTranslatorState' => 'enabled',
            'ospfv3AreaNssaTranslatorStabInterval' => 40,
            'ospfv3AreaNssaTranslatorEvents' => 0,
            'ospfv3AreaTEEnabled' => 'false',
            'context_name' => '',
        ];
    }
}
