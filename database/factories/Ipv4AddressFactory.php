<?php

namespace Database\Factories;

use App\Models\Ipv4Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Util\IPv4;

class Ipv4AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ipv4Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $prefix = $this->faker->numberBetween(0, 32);
        $ip = new IPv4($this->faker->ipv4 . '/' . $prefix);

        return [
            'ipv4_address' => $ip->uncompressed(),
            'ipv4_prefixlen' => $prefix,
            'port_id' => function () {
                return \App\Models\Port::factory()->create()->port_id;
            },
            'ipv4_network_id' => function () use ($ip) {
                return \App\Models\Ipv4Network::factory()->create(['ipv4_network' => $ip->getNetworkAddress() . '/' . $ip->cidr])->ipv4_network_id;
            },
        ];
    }
}
