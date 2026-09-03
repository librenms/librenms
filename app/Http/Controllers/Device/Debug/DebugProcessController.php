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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StreamingController;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\PerDeviceProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Util\ModuleList;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DebugProcessController extends Controller
{
    use StreamingController;

    public function __invoke(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['poller', 'discovery'])],
        ]);

        $this->disableDatastores();

        $process = match($validated['type']) {
            'poller' => new PerDeviceProcess(ProcessType::Poller, $device->device_id, PollDevice::class, [], new ModuleList),
            'discovery' => new PerDeviceProcess(ProcessType::Discovery, $device->device_id, PollDevice::class, [], new ModuleList),
        };

        $downloadFile = $validated['format'] === 'download' ? $validated['type'] . '-' . $device->hostname . '.txt' : null;
        $headers = $this->headers($downloadFile);

        return new StreamedResponse(function () use ($process): void {
            // Create an unbuffered logging channels with colours disabled
            config(['logging.channels.browser' => [
                'driver' => 'custom',
                'via' => \App\Logging\CreateEchoHandler::class,
                'level' => 'debug',
            ]]);
            Log::setDefaultDriver('browser');

            $process->run();
        }, 200, $headers);
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
