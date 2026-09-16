<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Vlan> */
class VlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vlan_vlan' => $this->faker->numberBetween(1, 4094),
            'vlan_domain' => $this->faker->numberBetween(1, 100),
            'vlan_name' => 'VLAN_' . $this->faker->word(),
            'vlan_type' => 'ethernet',
        ];
    }
}
