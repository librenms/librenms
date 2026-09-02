<?php

/**
 * CiscoOtvController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CiscoOtvController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $component = new \LibreNMS\Component();
        $otvComponents = $component->getComponents($device->device_id, ['type' => 'Cisco-OTV']);
        $rawOverlays = $otvComponents[$device->device_id] ?? [];

        $overlays = [];
        foreach ($rawOverlays as $index => $overlay) {
            if (($overlay['otvtype'] ?? '') === 'overlay') {
                $isNormal = ($overlay['status'] ?? 0) == 0 && ($overlay['ignore'] ?? 0) == 0 && ($overlay['disabled'] ?? 0) == 0;
                $itemClass = $isNormal ? '' : 'list-group-item-danger';

                $adjacencies = [];
                foreach ($rawOverlays as $adjIndex => $adjacency) {
                    if (($adjacency['otvtype'] ?? '') === 'adjacency' && ($adjacency['overlay'] ?? null) == ($overlay['index'] ?? null)) {
                        $adjNormal = ($adjacency['status'] ?? 0) == 0 && ($adjacency['ignore'] ?? 0) == 0 && ($adjacency['disabled'] ?? 0) == 0;
                        $adjClass = $adjNormal ? '' : 'list-group-item-danger';
                        $adjacencies[$adjIndex] = [
                            'index' => $adjIndex,
                            'label' => $adjacency['label'] ?? '',
                            'endpoint' => $adjacency['endpoint'] ?? '',
                            'error' => $adjacency['error'] ?? '',
                            'is_normal' => $adjNormal,
                            'item_class' => $adjClass,
                        ];
                    }
                }

                $overlays[$index] = [
                    'index' => $index,
                    'label' => $overlay['label'] ?? '',
                    'transport' => $overlay['transport'] ?? '',
                    'error' => $overlay['error'] ?? '',
                    'is_normal' => $isNormal,
                    'item_class' => $itemClass,
                    'adjacencies' => $adjacencies,
                ];
            }
        }

        return view('device.tabs.routing.cisco-otv', [
            'device' => $device,
            'overlays' => $overlays,
        ]);
    }
}
