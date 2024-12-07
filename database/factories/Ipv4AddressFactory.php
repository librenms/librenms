<?php

namespace Database\Factories;

use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Util\IPv4;

/** @extends Factory<\App\Models\Ipv4Address> */
class Ipv4AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $prefix = $this->faker->numberBetween(0, 32);
        $ip = new IPv4($this->faker->ipv4() . '/' . $prefix);

        return [
            'ipv4_address' => $ip->uncompressed(),
            'ipv4_prefixlen' => $prefix,
            'port_id' => function () {
                $port = Port::factory()->create(); /** @var Port $port */

                return $port->port_id;
            },
            'ipv4_network_id' => function () use ($ip) {
                $ipv4 = Ipv4Network::factory()->create(['ipv4_network' => $ip->getNetworkAddress() . '/' . $ip->cidr]); /** @var Ipv4Address $ipv4 */

                return $ipv4->ipv4_network_id;
            },
        ];
    }
}
