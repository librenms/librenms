<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Ospfv3Instance> */
class Ospfv3InstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'router_id' => $this->faker->unique()->ipv4(),
            'ospfv3RouterId' => $this->faker->numberBetween(1, 4294967295),
            'ospfv3AdminStatus' => 'enabled',
            'ospfv3VersionNumber' => '3',
            'ospfv3AreaBdrRtrStatus' => 'false',
            'ospfv3ASBdrRtrStatus' => 'false',
            'ospfv3OriginateNewLsas' => $this->faker->numberBetween(0, 100),
            'ospfv3RxNewLsas' => $this->faker->numberBetween(0, 100),
            'ospfv3ExtLsaCount' => $this->faker->numberBetween(0, 100),
            'ospfv3ExtAreaLsdbLimit' => 0,
            'ospfv3AsScopeLsaCount' => $this->faker->numberBetween(0, 1000),
            'ospfv3AsScopeLsaCksumSum' => $this->faker->numberBetween(0, 100000),
            'ospfv3ExitOverflowInterval' => 0,
            'ospfv3ReferenceBandwidth' => 100000,
            'ospfv3RestartSupport' => 'none',
            'ospfv3RestartInterval' => 120,
            'ospfv3RestartStrictLsaChecking' => 'true',
            'ospfv3RestartStatus' => 'notRestarting',
            'ospfv3RestartAge' => 0,
            'ospfv3RestartExitReason' => 'none',
            'ospfv3StubRouterSupport' => 'false',
            'ospfv3StubRouterAdvertisement' => 'doNotAdvertise',
            'ospfv3DiscontinuityTime' => 0,
            'ospfv3RestartTime' => 0,
        ];
    }
}
