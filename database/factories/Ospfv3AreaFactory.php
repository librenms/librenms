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
        // (device_id, ospfv3AreaId, context_name) is a unique key. A process-local counter
        // keeps area ids distinct within a batch without relying on faker's global unique()
        // state (which accumulates across the whole test run and can exhaust a small pool).
        static $seq = 0;

        return [
            'ospfv3_instance_id' => $this->faker->numberBetween(1, 100),
            'ospfv3AreaId' => $seq++,
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
