<?php

/**
 * OspfController.php
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
use App\Models\Ipv4Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OspfController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $portCount = $device->ospfPorts()->count();
        $portCountEnabled = $device->ospfPorts()->where('ospfIfAdminStat', 'enabled')->count();
        $ports = $device->ospfPorts()
            ->with('port')
            ->where('ospfIfAdminStat', 'enabled')
            ->orderBy('ospfIfAreaId')
            ->get();

        $nbrs = $device->ospfNbrs()->get()->map(function ($nbr) {
            $host = Ipv4Address::where('ipv4_address', $nbr->ospfNbrRtrId)
                ->with('port.device')
                ->first()
                ?->port
                ?->device;

            return [
                'router_id' => $nbr->ospfNbrRtrId,
                'device_id' => $host?->device_id,
                'ip_address' => $nbr->ospfNbrIpAddr,
                'state' => $nbr->ospfNbrState,
                'status_color' => match ($nbr->ospfNbrState) {
                    'full' => 'success',
                    'down' => 'danger',
                    default => 'default',
                },
            ];
        });

        $instances = [];
        foreach ($device->ospfInstances()->with('areas')->get() as $instance) {
            $areas = [];
            foreach ($instance->areas as $area) {
                $areaPortCount = $device->ospfPorts()->where('ospfIfAreaId', $area->ospfAreaId)->count();
                $areaPortCountEnabled = $device->ospfPorts()->where('ospfIfAdminStat', 'enabled')->where('ospfIfAreaId', $area->ospfAreaId)->count();

                $areas[] = [
                    'area_id' => $area->ospfAreaId,
                    'port_count' => $areaPortCount,
                    'port_count_enabled' => $areaPortCountEnabled,
                    'status' => $instance->ospfAdminStat,
                ];
            }

            $instances[] = [
                'instance' => $instance,
                'router_id' => $instance->ospfRouterId,
                'admin_stat' => $instance->ospfAdminStat,
                'status_color' => $instance->ospfAdminStat === 'enabled' ? 'success' : 'default',
                'abr_status' => $instance->ospfAreaBdrRtrStatus,
                'abr_status_color' => $instance->ospfAreaBdrRtrStatus === 'true' ? 'success' : 'default',
                'asbr_status' => $instance->ospfASBdrRtrStatus,
                'asbr_status_color' => $instance->ospfASBdrRtrStatus === 'true' ? 'success' : 'default',
                'area_count' => $instance->areas->count(),
                'port_count' => $portCount,
                'port_count_enabled' => $portCountEnabled,
                'nbr_count' => $nbrs->count(),
                'areas' => $areas,
                'ports' => $ports->map(fn ($p) => [
                    'port' => $p->port,
                    'port_id' => $p->port_id,
                    'type' => $p->ospfIfType,
                    'state' => $p->ospfIfState,
                    'cost' => $p->ospfIfMetricValue,
                    'area_id' => $p->ospfIfAreaId,
                ]),
                'nbrs' => $nbrs,
            ];
        }

        return view('device.tabs.routing.ospf', [
            'device' => $device,
            'instances' => $instances,
        ]);
    }
}
