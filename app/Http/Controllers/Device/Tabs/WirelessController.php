<?php

/**
 * WirelessController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\WirelessSensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\UI\DeviceTab;

class WirelessController extends Controller implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', WirelessSensor::class) && $device->wirelessSensors()->exists();
    }

    public function slug(): string
    {
        return 'wireless';
    }

    public function icon(): string
    {
        return 'fa-wifi';
    }

    public function name(): string
    {
        return __('Wireless');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);

        $request->validate([
            'metric' => 'nullable|string',
        ]);

        // order the classes as the enum does
        $present = $device->wirelessSensors()->distinct()->pluck('sensor_class');
        $classes = array_values(array_filter(WirelessSensorType::cases(), fn (WirelessSensorType $class) => $present->contains($class)));

        $metric = WirelessSensorType::tryFrom($request->string('metric')->value());

        return view('device.tabs.wireless', [
            'device' => $device,
            'classes' => $classes,
            'metric' => $metric,
            'options' => collect(['overview' => __('Overview')])
                ->merge(collect($classes)->mapWithKeys(fn (WirelessSensorType $class) => [$class->value => __("wireless.$class->value.short")]))
                ->map(fn ($text, $metric) => ['text' => $text, 'link' => route('device.wireless', ['device' => $device->device_id, 'metric' => $metric === 'overview' ? null : $metric])])
                ->all(),
            'sensors' => $metric ? $device->wirelessSensors()->where('sensor_class', $metric)->orderBy('sensor_descr')->get() : collect(),
        ]);
    }
}
