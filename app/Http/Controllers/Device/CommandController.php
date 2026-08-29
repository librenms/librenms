<?php

/**
 * CommandController.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace App\Http\Controllers\Device;

use App\BrowserOutput;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommandController
{
    public function show(Device $device, Request $request): StreamedResponse
    {
        return new StreamedResponse(function () use ($device) {
            // Disable PHP output buffering limit
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            Artisan::call('device:poll', ['device spec' => $device->device_id, '-v' => true, '-v' => true, '--no-data' => true], new BrowserOutput());
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/plain',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
