<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\EntPhysical> */
class EntPhysicalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'entPhysicalIndex' => $this->faker->unique()->numberBetween(1, 10000),
            'entPhysicalDescr' => $this->faker->randomElement(['Chassis', 'Power Supply 1', 'Fan Tray', 'GigabitEthernet0/1', 'SFP Module']),
            'entPhysicalClass' => $this->faker->randomElement(['chassis', 'module', 'port', 'powerSupply', 'fan', 'sensor']),
            'entPhysicalName' => $this->faker->randomElement(['Chassis', 'PSU1', 'FanTray1', 'Gi0/1', 'SFP1']),
            'entPhysicalModelName' => $this->faker->bothify('??-####'),
            'entPhysicalSerialNum' => $this->faker->bothify('SN-########'),
            'entPhysicalMfgName' => $this->faker->randomElement(['Cisco', 'Juniper', 'Arista']),
            'entPhysicalContainedIn' => 0,
            'entPhysicalParentRelPos' => -1,
            'entPhysicalIsFRU' => $this->faker->randomElement(['true', 'false']),
        ];
    }
}
