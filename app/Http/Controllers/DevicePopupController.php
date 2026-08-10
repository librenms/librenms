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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LibreNMS\Util\Graph;

class DevicePopupController
{
    public function __invoke(Request $request, Device $device)
    {
        if (! LibrenmsConfig::get('web_mouseover', true)) {
            return response('Disabled');
        }

        // Check access permissions
        Gate::authorize('view', $device);

        return view('device.popup', [
            'device' => $device,
            'osText' => LibrenmsConfig::getOsSetting($device->os ?? '', 'text'),
            'href' => route('device', ['device' => $device->device_id]),
            'graphs' => $this->buildGraphs($request, $device),
        ]);
    }

    /**
     * @return array[]
     */
    private function buildGraphs(Request $request, Device $device): array
    {
        $type = $request->string('type');
        if ($type->isNotEmpty()) {
            return [
                [
                    'device' => $device,
                    'type' => $type,
                    'title' => Str::title($type),
                    'graphs' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-14d'], ['from' => '-30d']],
                ],
            ];
        }

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

        return $graphs;
    }
}
