<?php
/**
 * RoutesTablesController.php
 *
 * InetCidrRoute tables data for bootgrid display
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja
 */

namespace App\Http\Controllers\Table;

use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Ipv6Address;
use App\Models\InetCidrRoute;
use App\Models\Port;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Util\IP;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

class RoutesTablesController extends TableController
{
    protected $ipCache = [];

    protected function rules()
    {
        return [
            'device_id' => 'nullable|integer',
            'searchby' => 'in:inetCidrRouteNextHop,inetCidrRouteDest,inetCidrRouteProto,inetCidrRouteType',
            ];
    }

    protected function filterFields($request)
    {
        return [
            'inetCidrRoute.device_id' => 'device_id',
            ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return InetCidrRoute::hasAccess($request->user())
            ->with(['device'])
            ->select('inetCidrRoute.*');
    }

    /**
     * @param string $search
     * @param Builder $query
     * @param array $fields
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    protected function search($search, $query, $fields = [])
    {
        if ($search = trim(\Request::get('searchPhrase'))) {
            $searchLike = '%' . $search . '%';
            return $query->where('inetCidrRoute.inetCidrRouteNextHop', 'like', $searchLike)
                ->orWhere('inetCidrRoute.inetCidrRouteDest', 'like', $searchLike);
        }

        return $query;
    }

    /**
     * @param Request $request
     * @param Builder $query
     * @return Builder
     */
    public function sort($request, $query)
    {
        $sort = $request->get('sort');

        if (isset($sort['inetCidrRouteIfIndex'])) {
            $query->join('ports', function ($join) {
                    $join->on('inetCidrRoute.inetCidrRouteIfIndex', 'ports.ifIndex')
                    ->on('inetCidrRoute.device_id', '=', 'ports.device_id');
            })
            ->orderBy('ports.ifDescr', $sort['inetCidrRouteIfIndex']);
        }

        if (isset($sort['inetCidrRouteProto'])) {
            $query->orderBy('inetCidrRouteProto', $sort['inetCidrRouteProto']);
        }
        if (isset($sort['inetCidrRouteType'])) {
            $query->orderBy('inetCidrRouteType', $sort['inetCidrRouteType']);
        }

        if (isset($sort['updated_at'])) {
            $query->orderBy('updated_at', $sort['updated_at']);
        }

        if (isset($sort['created_at'])) {
            $query->orderBy('created_at', $sort['created_at']);
        }

        if (isset($sort['context_name'])) {
            $query->orderBy('context_name', $sort['context_name']);
        }

        return $query;
    }

    public function formatItem($route_entry)
    {
        $translateProto[1] = '1-other';
        $translateProto[2] = '2-local';
        $translateProto[3] = '3-netmgmt';
        $translateProto[4] = '4-icmp';
        $translateProto[5] = '5-egp';
        $translateProto[6] = '6-ggp';
        $translateProto[7] = '7-hello';
        $translateProto[8] = '8-rip';
        $translateProto[9] = '9-isIs';
        $translateProto[10] = '10-esIs';
        $translateProto[11] = '11-ciscoIgrp';
        $translateProto[12] = '12-bbnSpfIgp';
        $translateProto[13] = '13-ospf';
        $translateProto[14] = '14-bgp';
        $translateProto[15] = '15-idpr';
        $translateProto[16] = '16-ciscoEigrp';
        $translateProto[17] = '17-dvmrp';

        $translateType[1] = '1-other';
        $translateType[2] = '2-reject';
        $translateType[3] = '3-local';
        $translateType[4] = '4-remote';
        $translateType[5] = '5-blackhole';

        $item = $route_entry->toArray();

        if ($route_entry->updated_at) {
            $item['updated_at'] = $route_entry->updated_at->diffForHumans();
        }
        if ($route_entry->created_at) {
            $item['created_at'] = $route_entry->created_at->toDateTimeString();
        }

        if ($route_entry->inetCidrRouteIfIndex) {
            $port = $route_entry->port()->first();
            if ($port) {
                $item['inetCidrRouteIfIndex'] = Url::portLink($port, $port->getShortLabel());
            }
        }

        if ($route_entry->inetCidrRouteNextHop_device_id) {
            $device = Device::where('device_id', '=', $route_entry->inetCidrRouteNextHop_device_id)->first();
            if ($device->device_id == $route_entry->device_id) {
                $item['inetCidrRouteNextHop'] = Url::deviceLink($device, "localhost");
            } else {
                $item['inetCidrRouteNextHop'] = $item['inetCidrRouteNextHop'] . "<br>(" . Url::deviceLink($device) . ")";
            }
        }
        if ($route_entry->inetCidrRouteProto && $translateProto[$route_entry->inetCidrRouteProto]) {
            $item['inetCidrRouteProto'] = $translateProto[$route_entry->inetCidrRouteProto];
        }
        if ($route_entry->inetCidrRouteType && $translateType[$route_entry->inetCidrRouteType]) {
            $item['inetCidrRouteType'] = $translateType[$route_entry->inetCidrRouteType];
        }
        if ($route_entry->context_name == '') {
            $item['context_name'] = '[global]';
        } else {
            $item['context_name'] = '<a href="' . Url::generate(['page' => 'routing', 'protocol' => 'vrf', 'vrf' => $route_entry->context_name]) . '">' . $route_entry->context_name . '</a>' ;
        }
        return $item;
    }
}
