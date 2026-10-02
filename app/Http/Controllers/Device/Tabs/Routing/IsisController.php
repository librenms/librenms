<?php

/**
 * IsisController.php
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

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\IsisAdjacency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class IsisController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $adjacencies = $device->isisAdjacencies()
            ->with('port')
            ->get()
            ->map(function (IsisAdjacency $adj): array {
                $stateColor = match ($adj->isisISAdjState) {
                    'up' => 'success',
                    'down' => 'danger',
                    default => 'warning',
                };

                return [
                    'adj' => $adj,
                    'port' => $adj->port,
                    'port_id' => $adj->port_id,
                    'ip_address' => $adj->isisISAdjIPAddrAddress,
                    'neighbour_sys_id' => $adj->isisISAdjNeighSysID,
                    'area_address' => $adj->isisISAdjAreaAddress,
                    'neighbour_sys_type' => $adj->isisISAdjNeighSysType,
                    'admin_state' => $adj->isisCircAdminState,
                    'state' => $adj->isisISAdjState,
                    'state_color' => $stateColor,
                    'last_uptime' => \LibreNMS\Util\Time::formatInterval($adj->isisISAdjLastUpTime),
                ];
            });

        return view('device.tabs.routing.isis', [
            'device' => $device,
            'adjacencies' => $adjacencies,
        ]);
    }
}
