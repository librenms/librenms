<?php

/**
 * DebugSnmpwalkController.php
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
use App\Http\Controllers\StreamingController;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DebugSnmpwalkController extends Controller
{
    use StreamingController;

    public function __invoke(Device $device, Request $request): StreamedResponse
    {
        $this->authorize('debug', $device);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['text', 'download'])],
            'type' => ['required', Rule::in(['snmpwalk'])],
        ]);

        switch ($validated['type']) {
            case 'snmpwalk':
                include_once base_path('includes/snmp.inc.php');
                $cmd = gen_snmpwalk_cmd($device->toArray(), '.', '-OUneb');
                break;
            default:
                throw new \Exception('Request type ' . $validated['type'] . ' needs to be implemented');
        }

        $downloadFile = $validated['format'] == 'download' ? 'alerts-' . $device->hostname . '.txt' : null;
        $headers = $this->headers($downloadFile);

        return new StreamedResponse(function () use ($cmd): void {
            $result = Process::run($cmd, function (string $type, string $output): void {
                echo $output;
                flush();
            });

            echo PHP_EOL . 'exit_status:' . $result->exitCode() . PHP_EOL;
        }, 200, $headers);
    }
}
