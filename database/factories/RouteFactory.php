<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Route> */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'inetCidrRouteIfIndex' => $this->faker->numberBetween(1, 100),
            'inetCidrRouteDest' => $this->faker->ipv4(),
            'inetCidrRouteDestType' => 'ipv4',
            'inetCidrRoutePfxLen' => $this->faker->numberBetween(0, 32),
            'inetCidrRoutePolicy' => '0.0',
            'inetCidrRouteNextHop' => $this->faker->ipv4(),
            'inetCidrRouteNextHopType' => 'ipv4',
            'inetCidrRouteMetric1' => $this->faker->numberBetween(0, 100),
            'inetCidrRouteProto' => $this->faker->numberBetween(1, 16),
            'inetCidrRouteType' => $this->faker->numberBetween(1, 5),
            'inetCidrRouteNextHopAS' => $this->faker->numberBetween(0, 65535),
        ];
    }
}
