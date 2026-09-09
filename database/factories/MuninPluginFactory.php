<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MuninPlugin> */
class MuninPluginFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'mplug_type' => 'cpu',
            'mplug_instance' => null,
            'mplug_category' => 'system',
            'mplug_title' => 'CPU Usage',
            'mplug_info' => 'CPU usage plugin',
            'mplug_vlabel' => '%',
            'mplug_args' => null,
            'mplug_total' => 0,
            'mplug_graph' => 1,
        ];
    }
}
