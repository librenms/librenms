<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\PollingMethodType;

/** @extends Factory<DevicePollingMethod> */
class DevicePollingMethodFactory extends Factory
{
    /** @var class-string<DevicePollingMethod> */
    protected $model = DevicePollingMethod::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
            'settings' => [],
        ];
    }
}
