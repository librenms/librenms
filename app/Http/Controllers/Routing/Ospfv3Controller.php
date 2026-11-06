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
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\Models\Ospfv3Instance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Ospfv3Controller extends Controller
{
    public function __invoke(Request $request): View
    {
        $instances = Ospfv3Instance::hasAccess($request->user())
            ->with('device')
            ->withCount([
                'areas',
                'ospfv3Ports',
                'ospfv3Ports as ports_enabled_count' => fn ($query) => $query->where('ospfv3IfAdminStatus', 'enabled'),
                'nbrs',
            ])
            ->orderBy('device_id')
            ->get();

        return view('routing.ospf', [
            'tab' => 'ospfv3',
            'title' => __('OSPFv3'),
            'instances' => $instances->map(fn (Ospfv3Instance $instance) => [
                'device' => $instance->device,
                'router_id' => $instance->router_id,
                'admin_status' => $instance->ospfv3AdminStatus,
                'abr_status' => $instance->ospfv3AreaBdrRtrStatus,
                'asbr_status' => $instance->ospfv3ASBdrRtrStatus,
                'area_count' => $instance->areas_count,
                'port_count' => $instance->ospfv3_ports_count,
                'port_enabled_count' => $instance->getAttribute('ports_enabled_count'),
                'nbr_count' => $instance->nbrs_count,
            ]),
        ]);
    }
}
