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

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\StreamsOutputToBrowser;
use App\Models\Device;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DebugPollAndDiscoveryController extends Controller
{
    use StreamsOutputToBrowser;

    public function __invoke(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['poller', 'discovery'])],
        ]);

        if ($validated['format'] == 'download') {
            $this->enableDownload($validated['type'] . '-' . $device->hostname . '.txt');
        }

        [$cmd, $args] = match ($validated['type']) {
            'poller' => ['device:poll', ['device spec' => $device->device_id, '-vv' => true, '--no-data' => true]],
            default => ['device:discover', ['device spec' => $device->device_id, '-vv' => true]],
        };

        return $this->stream(function () use ($cmd, $args): void {
            Event::forget(CommandStarting::class); // prevent normal cli setup and checks

            $exitCode = Artisan::call($cmd, $args, $this->getCliStreamOutput());

            if ($exitCode) {
                echo PHP_EOL . 'exit_status:' . $exitCode . PHP_EOL;
            }
        });
    }
}
