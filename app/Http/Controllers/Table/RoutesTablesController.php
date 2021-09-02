<?php
/**
 * RoutesTablesController.php
 *
 * Route tables data for bootgrid display
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Util\IP;
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
            'route.context_name' => 'context_name',
            'route.inetCidrRouteProto' => 'proto',
        ];
    }

    protected function sortFields($request)
    {
        return [
            'context_name',
            'inetCidrRouteDestType',
            'inetCidrRouteDest',
            'inetCidrRoutePfxLen',
            'inetCidrRouteNextHop',
            'inetCidrRouteIfIndex',
            'inetCidrRouteMetric1',
            'inetCidrRouteType',
            'inetCidrRouteProto',
            'created_at',
            'updated_at',
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
        $join = function ($query) {
            $query->on('ports.port_id', 'route.port_id');
        };
        $showAllRoutes = trim(\Request::get('showAllRoutes'));
        $showProtocols = trim(\Request::get('showProtocols'));
        if ($showProtocols == 'all') {
            $protocols = ['ipv4', 'ipv6'];
        } else {
            $protocols = [$showProtocols];
        }
        if ($request->device_id && $showAllRoutes == 'false') {
            $query = Route::hasAccess($request->user())
                ->leftJoin('ports', $join)
                ->where('route.device_id', $request->device_id)
                ->whereIn('route.inetCidrRouteDestType', $protocols)
                ->where('updated_at', Route::hasAccess($request->user())
                    ->where('route.device_id', $request->device_id)
                    ->select('updated_at')
                    ->max('updated_at'));

            return $query;
        }
        if ($request->device_id && $showAllRoutes == 'true') {
            $query = Route::hasAccess($request->user())
                ->leftJoin('ports', $join)
                ->where('route.device_id', $request->device_id)
                ->whereIn('route.inetCidrRouteDestType', $protocols);

            return $query;
        }

        return Route::hasAccess($request->user())
            ->leftJoin('ports', $join);
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

            return $query->where(function ($query) use ($searchLike) {
                return $query->where('route.inetCidrRouteNextHop', 'like', $searchLike)
                    ->orWhere('route.inetCidrRouteDest', 'like', $searchLike);
            });
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
            $query->orderBy('ifDescr', $sort['inetCidrRouteIfIndex'])
                ->orderBy('inetCidrRouteIfIndex', $sort['inetCidrRouteIfIndex']);
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
            'context_name',
            'inetCidrRouteDest',
        ];
        foreach ($s_fields as $s_field) {
            if (isset($sort[$s_field])) {
                $query->orderBy($s_field, $sort[$s_field]);
            }
        }

        return $query;
    }

    /**
     * @param Route $route_entry
     */
    public function formatItem($route_entry)
    {
        $item['updated_at'] = $route_entry->updated_at ? $route_entry->updated_at->diffForHumans() : $route_entry->updated_at;
        $item['created_at'] = $route_entry->created_at ? $route_entry->created_at->toDateTimeString() : $route_entry->created_at;
        $item['inetCidrRouteIfIndex'] = $route_entry->inetCidrRouteIfIndex == 0 ? 'Undefined' : $route_entry->inetCidrRouteIfIndex;
        $item['inetCidrRouteMetric1'] = $route_entry->inetCidrRouteMetric1;
        $item['inetCidrRoutePfxLen'] = $route_entry->inetCidrRoutePfxLen;
        $item['inetCidrRouteDestType'] = $route_entry->inetCidrRouteDestType;

        try {
            $obj_inetCidrRouteDest = IP::parse($route_entry->inetCidrRouteDest);
            $item['inetCidrRouteDest'] = $obj_inetCidrRouteDest->compressed();
        } catch (\Exception $e) {
            $item['inetCidrRouteDest'] = $route_entry->inetCidrRouteDest;
        }

        $item['inetCidrRouteIfIndex'] = $route_entry->inetCidrRouteIfIndex == 0 ? 'Undefined' : 'IfIndex ' . $route_entry->inetCidrRouteIfIndex;
        if ($port = $route_entry->port()->first()) {
            $item['inetCidrRouteIfIndex'] = Url::portLink($port, htmlspecialchars($port->getShortLabel()));
        }

        try {
            $obj_inetCidrRouteNextHop = IP::parse($route_entry->inetCidrRouteNextHop);
            $item['inetCidrRouteNextHop'] = $obj_inetCidrRouteNextHop->compressed();
        } catch (\Exception $e) {
            $item['inetCidrRouteNextHop'] = $route_entry->inetCidrRouteNextHop;
        }
        $device = Device::findByIp($route_entry->inetCidrRouteNextHop);
        if ($device) {
            if ($device->device_id == $route_entry->device_id || in_array($route_entry->inetCidrRouteNextHop, ['127.0.0.1', '::1'])) {
                $item['inetCidrRouteNextHop'] = Url::deviceLink($device, 'localhost');
            } else {
                $item['inetCidrRouteNextHop'] = $item['inetCidrRouteNextHop'] . '<br>(' . Url::deviceLink($device) . ')';
            }
        }

        $item['inetCidrRouteProto'] = $route_entry->inetCidrRouteProto;
        if ($route_entry->inetCidrRouteProto && $route_entry::$translateProto[$route_entry->inetCidrRouteProto]) {
            $item['inetCidrRouteProto'] = $route_entry::$translateProto[$route_entry->inetCidrRouteProto];
        }

        $item['inetCidrRouteType'] = $route_entry->inetCidrRouteType;
        if ($route_entry->inetCidrRouteType && $route_entry::$translateType[$route_entry->inetCidrRouteType]) {
            $item['inetCidrRouteType'] = $route_entry::$translateType[$route_entry->inetCidrRouteType];
        }

        $item['context_name'] = '[global]';
        if ($route_entry->context_name != '') {
            $item['context_name'] = '<a href="' . Url::generate(['page' => 'routing', 'protocol' => 'vrf', 'vrf' => $route_entry->context_name]) . '">' . htmlspecialchars($route_entry->context_name) . '</a>';
        }

        return $item;
    }
}
