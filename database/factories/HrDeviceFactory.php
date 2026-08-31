<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\HrDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HrDevice>
 */
class HrDeviceFactory extends Factory
{
    protected $model = HrDevice::class;

    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'hrDeviceIndex' => $this->faker->unique()->numberBetween(1, 1000),
            'hrDeviceDescr' => $this->faker->sentence(3),
            'hrDeviceType' => 'hrDeviceOther',
            'hrDeviceErrors' => 0,
            'hrDeviceStatus' => 'running',
            'hrProcessorLoad' => $this->faker->numberBetween(0, 100),
        ];
    }
}
