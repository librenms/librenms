<?php

namespace Database\Factories;

use App\Models\Ipv6Network;
use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Util\IPv6;

/** @extends Factory<\App\Models\Ipv6Address> */
class Ipv6AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $prefix = $this->faker->numberBetween(1, 128);
        $ip = new IPv6($this->faker->ipv6() . '/' . $prefix);

        return [
            'ipv6_address' => $ip->uncompressed(),
            'ipv6_compressed' => $ip->compressed(),
            'ipv6_prefixlen' => $prefix,
            'ipv6_origin' => 'manual',
            'port_id' => function () {
                $port = Port::factory()->create(); /** @var Port $port */

                return $port->port_id;
            },
            'ipv6_network_id' => function () use ($ip) {
                $network = Ipv6Network::factory()->create([
                    'ipv6_network' => $ip->getNetworkAddress() . '/' . $ip->cidr,
                ]); /** @var Ipv6Network $network */

                return $network->ipv6_network_id;
            },
        ];
    }
}
