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
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\Models\OspfInstance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OspfController extends Controller
{
    public function __invoke(Request $request): View
    {
        $instances = OspfInstance::hasAccess($request->user())
            ->where('ospfAdminStat', 'enabled')
            ->with('device')
            ->withCount([
                'areas',
                'ports',
                'ports as ports_enabled_count' => fn ($query) => $query->where('ospfIfAdminStat', 'enabled'),
                'nbrs',
            ])
            ->orderBy('device_id')
            ->get();

        return view('routing.ospf', [
            'tab' => 'ospf',
            'title' => __('OSPF'),
            'instances' => $instances->map(fn (OspfInstance $instance) => [
                'device' => $instance->device,
                'router_id' => $instance->ospfRouterId,
                'admin_status' => $instance->ospfAdminStat,
                'abr_status' => $instance->ospfAreaBdrRtrStatus,
                'asbr_status' => $instance->ospfASBdrRtrStatus,
                'area_count' => $instance->areas_count,
                'port_count' => $instance->ports_count,
                'port_enabled_count' => $instance->getAttribute('ports_enabled_count'),
                'nbr_count' => $instance->nbrs_count,
            ]),
        ]);
    }
}
