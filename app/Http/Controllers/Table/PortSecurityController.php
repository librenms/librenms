<?php

/**
 * PortSecurityController.php
 *
 * Port Security tables data for bootgrid display
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
 */

namespace App\Http\Controllers\Table;

use App\Models\PortSecurity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Enum\PortSecurityStatus;

class PortSecurityController extends TableController
{
    protected function rules()
    {
        return [
            'port_id' => 'nullable|integer',
            'device_id' => 'nullable|integer',
            'searchby' => 'nullable|in:device,port,status,enable,max_secure,current_secure,violation_action,violation_count,secure_last_mac,sticky_enable',
        ];
    }

    protected function filterFields($request)
    {
        return [
            'port_security.device_id' => 'device_id',
            'port_security.port_id' => 'port_id',
        ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  Request  $request
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return PortSecurity::hasAccess($request->user())
            ->with(['device', 'port'])
            ->select('port_security.*');
    }

    /**
     * @param  string  $search
     * @param  Builder  $query
     * @param  array  $fields
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    protected function search($search, $query, $fields = [])
    {
        if ($search = trim(\Request::get('searchPhrase') ?? '')) {
            switch (\Request::get('searchby') ?? '') {
                case 'device':
                    return $query->whereHas('device', function ($q) use ($search) {
                        $q->where('hostname', 'like', "%$search%");
                    });
                case 'port':
                    return $query->whereHas('port', function ($q) use ($search) {
                        $q->where('ifDescr', 'like', "%$search%")
                          ->orWhere('ifAlias', 'like', "%$search%");
                    });
                case 'status':
                    return $query->where('status', 'like', "%$search%");
                case 'enable':
                    return $query->where('port_security_enable', 'like', "%$search%");
                case 'max_secure':
                    return $query->where('max_addresses', $search);
                case 'current_secure':
                    return $query->where('address_count', $search);
                case 'violation_action':
                    return $query->where('violation_action', 'like', "%$search%");
                case 'violation_count':
                    return $query->where('violation_count', $search);
                case 'secure_last_mac':
                    return $query->where('last_mac_address', 'like', "%$search%");
                case 'sticky_enable':
                    return $query->where('sticky_enable', 'like', "%$search%");
                default:
                    return $query->where(function ($query) use ($search) {
                        $query->whereHas('device', function ($q) use ($search) {
                            $q->where('hostname', 'like', "%$search%");
                        })
                        ->orWhereHas('port', function ($q) use ($search) {
                            $q->where('ifDescr', 'like', "%$search%")
                              ->orWhere('ifAlias', 'like', "%$search%");
                        })
                        ->orWhere('status', 'like', "%$search%")
                        ->orWhere('port_security_enable', 'like', "%$search%")
                        ->orWhere('violation_action', 'like', "%$search%")
                        ->orWhere('last_mac_address', 'like', "%$search%");
                    });
            }
        }

        return $query;
    }

    /**
     * @param  Request  $request
     * @param  Builder  $query
     * @return Builder
     */
    public function sort($request, $query)
    {
        $sort = $request->get('sort');

        if (isset($sort['device'])) {
            $query->leftJoin('devices', 'port_security.device_id', 'devices.device_id')
                ->orderBy('hostname', $sort['device'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['interface'])) {
            $query->leftJoin('ports', 'port_security.port_id', 'ports.port_id')
                ->orderBy('ports.ifDescr', $sort['interface'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['status'])) {
            $query->orderBy('status', $sort['status'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['enable'])) {
            $query->orderBy('port_security_enable', $sort['enable'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['max_secure'])) {
            $query->orderBy('max_addresses', $sort['max_secure'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['current_secure'])) {
            $query->orderBy('address_count', $sort['current_secure'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['violation_action'])) {
            $query->orderBy('violation_action', $sort['violation_action'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['violation_count'])) {
            $query->orderBy('violation_count', $sort['violation_count'] == 'desc' ? 'desc' : 'asc');
        }

        return $query;
    }

    /**
     * @param  PortSecurity  $portSecurity
     */
    public function formatItem($portSecurity)
    {
        $statusIcon = PortSecurityStatus::getIconClass($portSecurity->status);

        return [
            'device' => Blade::render('<x-device-link :device="$device"/>', ['device' => $portSecurity->device]),
            'interface' => $portSecurity->port ? Blade::render('<x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link>', ['port' => $portSecurity->port]) : 'N/A',
            'port_description' => $portSecurity->port->ifAlias ?? 'N/A',
            'status' => '<i class="fa ' . $statusIcon . '" aria-hidden="true" title="' . $portSecurity->status . '"></i> ' . $portSecurity->status,
            'enable' => $portSecurity->port_security_enable ?? 'N/A',
            'max_secure' => $portSecurity->max_addresses ?? 'N/A',
            'current_secure' => $portSecurity->address_count ?? 'N/A',
            'violation_action' => $portSecurity->violation_action ?? 'N/A',
            'violation_count' => $portSecurity->violation_count ?? 'N/A',
            'secure_last_mac' => $portSecurity->last_mac_address ?? 'N/A',
            'sticky_enable' => $portSecurity->sticky_enable ?? 'N/A',
        ];
    }
}
