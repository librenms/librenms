<?php

/**
 * Ospfv3Controller.php
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

class Ospfv3Controller extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $ospfv3Instances = $device->ospfv3Instances()
            ->with([
                'areas.ospfv3Ports.port',
                'nbrs.port.device',
                'ospfv3Ports.port',
            ])
            ->get();

        $instances = [];
        foreach ($ospfv3Instances as $instance) {
            $statusColor = match ($instance->ospfv3AdminStatus) {
                'enabled' => 'success',
                'disabled' => 'danger',
                default => 'default',
            };

            $abrColor = match ($instance->ospfv3AreaBdrRtrStatus) {
                'true' => 'success',
                'false' => 'danger',
                default => 'default',
            };

            $asbrColor = match ($instance->ospfv3ASBdrRtrStatus) {
                'true' => 'success',
                'false' => 'danger',
                default => 'default',
            };

            $areas = [];
            foreach ($instance->areas as $area) {
                $areaStatusColor = match ($area->ospfv3AreaAdminStatus ?? $area->ospfv3AdminStatus ?? 'enabled') {
                    'enabled' => 'success',
                    'disabled' => 'danger',
                    default => 'default',
                };

                $areas[] = [
                    'area_id_ip' => long2ip($area->ospfv3AreaId),
                    'port_count' => $area->ospfv3Ports->count(),
                    'port_count_enabled' => $area->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count(),
                    'lsa_count' => $area->ospfv3AreaScopeLsaCount,
                    'status' => $area->ospfv3AreaAdminStatus ?? $area->ospfv3AdminStatus ?? 'enabled',
                    'status_color' => $areaStatusColor,
                ];
            }

            $ports = [];
            foreach ($instance->ospfv3Ports as $port) {
                $ports[] = [
                    'port' => $port->port,
                    'port_id' => $port->port_id,
                    'type' => $port->ospfv3IfType,
                    'state' => $port->ospfv3IfState,
                    'cost' => $port->ospfv3IfMetricValue,
                    'area_id_ip' => long2ip($port->ospfv3IfAreaId),
                ];
            }

            $nbrs = [];
            foreach ($instance->nbrs as $nbr) {
                $nbrStatusColor = match ($nbr->ospfv3NbrState) {
                    'full' => 'success',
                    'down' => 'danger',
                    default => 'warning',
                };

                $nbrs[] = [
                    'router_id' => $nbr->router_id,
                    'device_id' => $nbr->device_id,
                    'address' => $nbr->ospfv3NbrAddress,
                    'state' => $nbr->ospfv3NbrState,
                    'status_color' => $nbrStatusColor,
                ];
            }

            $instances[] = [
                'router_id' => $instance->router_id,
                'admin_status' => $instance->ospfv3AdminStatus,
                'status_color' => $statusColor,
                'abr_status' => $instance->ospfv3AreaBdrRtrStatus,
                'abr_color' => $abrColor,
                'asbr_status' => $instance->ospfv3ASBdrRtrStatus,
                'asbr_color' => $asbrColor,
                'area_count' => count($areas),
                'port_count' => count($ports),
                'port_count_enabled' => $instance->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count(),
                'nbr_count' => count($nbrs),
                'areas' => $areas,
                'ports' => $ports,
                'nbrs' => $nbrs,
            ];
        }

        return view('device.tabs.routing.ospfv3', [
            'device' => $device,
            'instances' => $instances,
        ]);
    }
}
