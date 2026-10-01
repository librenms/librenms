<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\OspfInstance> */
class OspfInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ospf_instance_id' => $this->faker->numberBetween(1, 100),
            'ospfRouterId' => $this->faker->ipv4(),
            'ospfAdminStat' => 'enabled',
            'ospfVersionNumber' => 'version2',
            'ospfAreaBdrRtrStatus' => 'false',
            'ospfASBdrRtrStatus' => 'false',
            'ospfExternLsaCount' => $this->faker->numberBetween(0, 1000),
            'ospfExternLsaCksumSum' => $this->faker->numberBetween(0, 100000),
            'ospfTOSSupport' => 'false',
            'ospfOriginateNewLsas' => $this->faker->numberBetween(0, 100),
            'ospfRxNewLsas' => $this->faker->numberBetween(0, 100),
        ];
    }
}
