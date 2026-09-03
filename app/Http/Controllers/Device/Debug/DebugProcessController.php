<?php

/**
 * DebugProcessController.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Debug;

use App\Events\DeviceDiscovered;
use App\Events\DevicePolled;
use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\StreamsOutputToBrowser;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\PerDeviceProcess;
use App\Polling\Measure\MeasurementManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Util\ModuleList;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DebugProcessController extends Controller
{
    use StreamsOutputToBrowser;

    public function __invoke(Device $device, Request $request, MeasurementManager $measurements): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['poller', 'discovery'])],
        ]);

        if ($validated['format'] == 'download') {
            $this->enableDownload($validated['type'] . '-' . $device->hostname . '.txt');
        }

        if ($validated['type'] == 'discovery') {
            $process = new PerDeviceProcess(ProcessType::Discovery, (string) $device->device_id, DiscoverDevice::class, DeviceDiscovered::class, new ModuleList);
        } else {
            $process = new PerDeviceProcess(ProcessType::Poller, (string) $device->device_id, PollDevice::class, DevicePolled::class, new ModuleList);
        }

        return $this->stream(function () use ($process, $measurements): void {
            $output = $this->configureLoggerToStreamOutput();

            $this->disableDatastores();

            DB::beginTransaction();
            $process->run();
            DB::rollBack();

            $process->processResults($measurements, $output);
        });
    }

    private function disableDatastores(): void
    {
        LibrenmsConfig::set('rrd.enable', false);
        LibrenmsConfig::set('influxdb.enable', false);
        LibrenmsConfig::set('influxdbv2.enable', false);
        LibrenmsConfig::set('prometheus.enable', false);
        LibrenmsConfig::set('graphite.enable', false);
        LibrenmsConfig::set('kafka.enable', false);
    }
}
