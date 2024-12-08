<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\BgpPeer> */
class BgpPeerFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'bgpPeerIdentifier' => $this->faker->ipv4(),
            'bgpLocalAddr' => $this->faker->ipv4(),
            'bgpPeerRemoteAddr' => $this->faker->ipv4(),
            'bgpPeerRemoteAs' => $this->faker->numberBetween(1, 65535),
            'bgpPeerState' => $this->faker->randomElement(['established', 'idle']),
            'astext' => $this->faker->sentence(),
            'bgpPeerAdminStatus' => $this->faker->randomElement(['start', 'stop']),
            'bgpPeerInUpdates' => $this->faker->randomDigit(),
            'bgpPeerOutUpdates' => $this->faker->randomDigit(),
            'bgpPeerInTotalMessages' => $this->faker->randomDigit(),
            'bgpPeerOutTotalMessages' => $this->faker->randomDigit(),
            'bgpPeerFsmEstablishedTime' => $this->faker->unixTime(),
            'bgpPeerInUpdateElapsedTime' => $this->faker->unixTime(),
        ];
    }
}
