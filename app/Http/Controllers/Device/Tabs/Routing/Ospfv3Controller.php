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
use App\Models\Ospfv3Area;
use App\Models\Ospfv3Instance;
use App\Models\Ospfv3Nbr;
use App\Models\Ospfv3Port;
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

        $instances = $ospfv3Instances->map(fn (Ospfv3Instance $instance) => $this->formatInstance($instance))->all();

        return view('device.tabs.routing.ospfv3', [
            'device' => $device,
            'instances' => $instances,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatInstance(Ospfv3Instance $instance): array
    {
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

        $areas = $instance->areas->map(fn (Ospfv3Area $area) => $this->formatArea($area))->all();
        $ports = $instance->ospfv3Ports->map(fn (Ospfv3Port $port) => $this->formatPort($port))->all();
        $nbrs = $instance->nbrs->map(fn (Ospfv3Nbr $nbr) => $this->formatNbr($nbr))->all();

        return [
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

    /**
     * @return array<string, mixed>
     */
    private function formatArea(Ospfv3Area $area): array
    {
        $status = $area->ospfv3AreaAdminStatus ?? $area->ospfv3AdminStatus ?? 'enabled';
        $areaStatusColor = match ($status) {
            'enabled' => 'success',
            'disabled' => 'danger',
            default => 'default',
        };

        return [
            'area_id_ip' => long2ip($area->ospfv3AreaId),
            'port_count' => $area->ospfv3Ports->count(),
            'port_count_enabled' => $area->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count(),
            'lsa_count' => $area->ospfv3AreaScopeLsaCount,
            'status' => $status,
            'status_color' => $areaStatusColor,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPort(Ospfv3Port $port): array
    {
        return [
            'port' => $port->port,
            'port_id' => $port->port_id,
            'type' => $port->ospfv3IfType,
            'state' => $port->ospfv3IfState,
            'cost' => $port->ospfv3IfMetricValue,
            'area_id_ip' => long2ip($port->ospfv3IfAreaId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNbr(Ospfv3Nbr $nbr): array
    {
        $nbrStatusColor = match ($nbr->ospfv3NbrState) {
            'full' => 'success',
            'down' => 'danger',
            default => 'warning',
        };

        return [
            'router_id' => $nbr->router_id,
            'device_id' => $nbr->device_id,
            'address' => $nbr->ospfv3NbrAddress,
            'state' => $nbr->ospfv3NbrState,
            'status_color' => $nbrStatusColor,
        ];
    }
}
