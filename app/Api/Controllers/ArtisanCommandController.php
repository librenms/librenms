<?php

/**
 * ArtisanCommandController.php
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

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\StreamsOutputToBrowser;
use App\Models\Device;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArtisanCommandController extends Controller
{
    use StreamsOutputToBrowser;

    private function run(Device $device, string $cmd, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'buffer' => 'sometimes|boolean',
            'colour' => 'sometimes|boolean',
            'quiet' => 'sometimes|boolean',
            'verbose' => 'sometimes|boolean',
        ]);

        if ($validated['buffer']) {
            $this->enableBufferedOutput();
        }

        if ($validated['colour']) {
            $this->enableColour();
        }

        $args = ['device spec' => $device->device_id];
        if ($validated['quiet']) {
            $args['-q'] = true;
        } elseif ($validated['verbose']) {
            $args['-vv'] = true;
        }

        return $this->stream(function () use ($cmd, $args): void {
            Event::forget(CommandStarting::class); // prevent normal cli setup and checks

            $exitCode = Artisan::call($cmd, $args, $this->getCliStreamOutput());

            echo PHP_EOL . 'exit_status:' . $exitCode . PHP_EOL;
        });
    }

    public function poll(Device $device, Request $request): StreamedResponse
    {
        return $this->run($device, 'device:poll', $request);
    }

    public function discover(Device $device, Request $request): StreamedResponse
    {
        return $this->run($device, 'device:discover', $request);
    }
}
