<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PrinterSupply> */
class PrinterSupplyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'supply_oid' => '.1.3.6.1.2.1.43.11.1.1.9.1.1',
            'supply_capacity_oid' => '.1.3.6.1.2.1.43.11.1.1.8.1.1',
            'supply_index' => $this->faker->numberBetween(1, 10),
            'supply_type' => 'toner',
            'supply_descr' => 'Black Toner',
            'supply_capacity' => 100,
            'supply_current' => 80,
        ];
    }
}
