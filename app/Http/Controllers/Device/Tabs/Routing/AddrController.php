<?php

/**
 * AddrController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation: either version 3 of the License, or
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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Enum\IfOperStatus;

class AddrController extends Controller
{
    public function __invoke(Device $device): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $ports = $device->ports()
            ->where(function ($query): void {
                $query->whereHas('ipv4')
                    ->orWhereHas('ipv6');
            })
            ->with([
                'ipv4' => fn ($query) => $query->orderByRaw('INET_ATON(ipv4_address)'),
                'ipv6' => fn ($query) => $query->orderByRaw('INET6_ATON(ipv6_compressed)'),
            ])
            ->orderBy('ifName')
            ->get()
            ->map(function ($port): array {
                $status = $port->ifAdminStatus === IfOperStatus::Down
                    ? 'admindown'
                    : ($port->ifOperStatus === IfOperStatus::Up ? 'up' : 'down');

                return [
                    'port' => $port,
                    'status' => $status,
                ];
            });

        return view('device.tabs.routing.addr', [
            'device' => $device,
            'ports' => $ports,
        ]);
    }
}
