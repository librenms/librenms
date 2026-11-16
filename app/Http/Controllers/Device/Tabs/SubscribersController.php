<?php

/**
 * SubscribersController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use App\Models\Port;
use App\Models\SubscriberCount;
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;

class SubscribersController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->subscriberCounts()->exists();
    }

    public function slug(): string
    {
        return 'subscribers';
    }

    public function icon(): string
    {
        return 'fa-users';
    }

    public function name(): string
    {
        return __('Subscribers');
    }

    public const array PER_PAGE_OPTIONS = [25, 50, 100, 250];

    /**
     * A BNG can have a port per customer, ports are paginated and only the shown ports are loaded
     *
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        $perPage = in_array((int) $request->input('per_page'), self::PER_PAGE_OPTIONS) ? (int) $request->input('per_page') : self::PER_PAGE_OPTIONS[0];
        $search = trim((string) $request->input('search', ''));

        $portCounts = $device->subscriberCounts()->whereNotNull('subscriber_counts.port_id');
        $page = (clone $portCounts)
            ->join('ports', 'ports.port_id', '=', 'subscriber_counts.port_id')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('ports.ifName', 'like', "%$search%")
                ->orWhere('ports.ifDescr', 'like', "%$search%")
                ->orWhere('ports.ifAlias', 'like', "%$search%")))
            ->groupBy('subscriber_counts.port_id', 'ports.ifIndex')
            ->orderBy('ports.ifIndex')
            ->select('subscriber_counts.port_id')
            ->selectRaw('SUM(subscriber_counts.sessions) AS total')
            ->paginate($perPage)
            ->withQueryString();

        $ports = Port::whereIn('port_id', $page->pluck('port_id'))
            ->select(['port_id', 'device_id', 'ifIndex', 'ifName', 'ifDescr', 'ifAlias', 'ifAdminStatus', 'ifOperStatus'])
            ->get()->each->setRelation('device', $device)->keyBy('port_id');
        $sessions = (clone $portCounts)->whereIn('port_id', $page->pluck('port_id'))->get()->groupBy('port_id');

        $totals = (clone $portCounts)->groupBy('port_id')->selectRaw('SUM(sessions) AS total')->pluck('total');

        return [
            'system' => $device->subscriberCounts()->whereNull('port_id')->pluck('sessions', 'session_type')
                ->sortBy(fn ($sessions, $type) => array_search($type, array_keys(SubscriberCount::SESSION_TYPES))),
            'ports' => $page->getCollection()->map(fn (SubscriberCount $row) => [
                'port' => $ports->get($row->port_id),
                'sessions' => $sessions->get($row->port_id, collect())->pluck('sessions', 'session_type')->all(),
                'total' => (int) $row->getAttribute('total'),
            ])->filter(fn (array $row) => $row['port'] !== null)->values(),
            'paginator' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'port_count' => $totals->count(),
            'ports_down' => $totals->filter(fn ($total) => (int) $total === 0)->count(),
            'port_types' => collect(SubscriberCount::SESSION_TYPES)->keys()
                ->intersect((clone $portCounts)->distinct()->pluck('session_type'))->values(),
            'type_names' => SubscriberCount::SESSION_TYPES,
            'radius_servers' => $device->radiusServers()->orderBy('address')->orderBy('port')->get(),
            // graphs of the cisco-vpdn module (L2TP tunnels and sessions)
            'vpdn_graphs' => $device->graphs()->where('graph', 'like', 'vpdn\\_%')->orderBy('graph')->pluck('graph'),
        ];
    }
}
