<?php
/**
 * DevicePopupController.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Util\Graph;

class DevicePopupController
{
    public function __invoke(Device $device)
    {
        if (! LibrenmsConfig::get('web_mouseover', true)) {
            return response('Disabled');
        }

        // Check access permissions
        if (!$device->canAccess(auth()->user())) {
            return response('Unauthorized', 403);
        }

        // Build graphs HTML using existing graph-row component
        $graphs = [];
        foreach (Graph::getOverviewGraphsForDevice($device) as $graph) {
            if (isset($graph['text'], $graph['graph'])) {
                $graphs[] = [
                    'device' => $device,
                    'type' => $graph['graph'],
                    'title' => $graph['text'],
                    'graphs' => [['from' => '-1d'], ['from' => '-7d']],
                ];
            }
        }

        return view('device.popup', [
            'device' => $device,
            'osText' => LibrenmsConfig::getOsSetting($device->os ?? '', 'text'),
            'href' => route('device', ['device' => $device->device_id]),
            'graphs' => $graphs,
        ]);
    }
}
