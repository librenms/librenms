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
            'searchby' => 'in:inetCidrRouteNextHop,inetCidrRouteDest',
            ];
    }

    protected function filterFields($request)
    {
        return [
            'inetCidrRoute.context_name' => 'context_name',
            'inetCidrRoute.inetCidrRouteProto' => 'proto',
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
                    $join->on('inetCidrRoute.port_id', 'ports.port_id');
            })
            ->orderBy('ports.ifDescr', $sort['inetCidrRouteIfIndex']);
        }
        // Simple fields to sort
        $s_fields = [
            'inetCidrRouteDestType',
            'inetCidrRouteType',
            'inetCidrRouteMetric1',
            'inetCidrRoutePfxLen',
            'inetCidrRouteNextHop',
            'updated_at',
            'created_at',
            'context_name'
        ];
        foreach ($s_fields as $s_field) {
            if (isset($sort[$s_field])) {
                $query->orderBy($s_field, $sort[$s_field]);
            }
        }
        return $query;
    }

    public function formatItem($route_entry)
    {
        $item = $route_entry->toArray();

        if ($route_entry->updated_at) {
            $item['updated_at'] = $route_entry->updated_at->diffForHumans();
        }
        if ($route_entry->created_at) {
            $item['created_at'] = $route_entry->created_at->toDateTimeString();
        }
        if ($item['inetCidrRouteIfIndex'] == 0) {
            $item['inetCidrRouteIfIndex'] = 'Undefined';
        }
        if ($port = $route_entry->port()->first()) {
            $item['inetCidrRouteIfIndex'] = Url::portLink($port, $port->getShortLabel());
        }
        if ($route_entry->inetCidrRouteNextHop_device_id) {
            $device = Device::where('device_id', '=', $route_entry->inetCidrRouteNextHop_device_id)->first();
            if ($device->device_id == $route_entry->device_id) {
                $item['inetCidrRouteNextHop'] = Url::deviceLink($device, "localhost");
            } else {
                $item['inetCidrRouteNextHop'] = $item['inetCidrRouteNextHop'] . "<br>(" . Url::deviceLink($device) . ")";
            }
        }
        if ($route_entry->inetCidrRouteProto && $route_entry::$translateProto[$route_entry->inetCidrRouteProto]) {
            $item['inetCidrRouteProto'] = $route_entry::$translateProto[$route_entry->inetCidrRouteProto];
        }
        if ($route_entry->inetCidrRouteType && $route_entry::$translateType[$route_entry->inetCidrRouteType]) {
            $item['inetCidrRouteType'] = $route_entry::$translateType[$route_entry->inetCidrRouteType];
        }
        $item['context_name'] = '[global]';
        if ($route_entry->context_name != '') {
            $item['context_name'] = '<a href="' . Url::generate(['page' => 'routing', 'protocol' => 'vrf', 'vrf' => $route_entry->context_name]) . '">' . $route_entry->context_name . '</a>' ;
        }
        return $item;
    }
}
