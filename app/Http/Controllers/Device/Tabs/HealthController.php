<?php

/**
 * HealthController.php
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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LibreNMS\Component;
use LibreNMS\Interfaces\UI\DeviceTab;

class HealthController extends Controller implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->storage()->exists() || $device->sensors()->exists() || $device->mempools()->exists() || $device->processors()->exists();
    }

    public function slug(): string
    {
        return 'health';
    }

    public function icon(): string
    {
        return 'fa-heartbeat';
    }

    public function name(): string
    {
        return __('Health');
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

        $qfp = $this->qfpComponents($device);
        $metrics = $this->metrics($device, $qfp->isNotEmpty());
        $metric = $request->string('metric')->value() ?: 'overview';
        if (! isset($metrics[$metric])) {
            $metric = 'overview';
        }

        return view('device.tabs.health', [
            'device' => $device,
            'metric' => $metric,
            'metrics' => $metrics,
            'options' => collect(['overview' => __('Overview')] + $metrics)
                ->map(fn ($text, $option) => ['text' => $text, 'link' => route('device.health', ['device' => $device->device_id, 'metric' => $option === 'overview' ? null : $option])])
                ->all(),
            'items' => match ($metric) {
                'overview' => collect(),
                'processor' => $device->processors()->orderBy('processor_descr')->get(),
                'mempool' => $device->mempools()->get(),
                'storage' => $device->storage()->orderBy('storage_descr')->get(),
                'diskio' => $device->diskIo()->orderBy('diskio_descr')->get(),
                'qfp' => $qfp,
                default => $device->sensors()->where('sensor_class', $metric)->orderBy('sensor_descr')->get(),
            },
        ]);
    }

    /**
     * The health metrics the device has, as metric => title.
     *
     * @return array<string, string>
     */
    private function metrics(Device $device, bool $hasQfp): array
    {
        $metrics = array_filter([
            'processor' => $device->processors()->exists() ? __('Processor') : null,
            'qfp' => $hasQfp ? 'QFP' : null,
            'mempool' => $device->mempools()->exists() ? __('Memory') : null,
            'storage' => $device->storage()->exists() ? __('Disk Usage') : null,
            'diskio' => $device->diskIo()->exists() ? __('Disk I/O') : null,
        ]);

        foreach ($device->sensors()->distinct()->pluck('sensor_class') as $class) {
            $metrics[$class] = __("sensors.$class.short");
        }

        return $metrics;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function qfpComponents(Device $device): Collection
    {
        if (LibrenmsConfig::get("os.$device->os.group") !== 'cisco') {
            return collect();
        }

        return collect((new Component)->getComponents($device->device_id, ['type' => 'cisco-qfp'])[$device->device_id] ?? []);
    }
}
