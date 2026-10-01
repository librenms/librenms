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
 *
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\IP;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Route>
 */
class RoutesTablesController extends TableController
{
    protected function rules(): array
    {
        return [
            'device_id' => 'nullable|integer',
            'searchby' => 'in:inetCidrRouteNextHop,inetCidrRouteDest',
        ];
    }

    protected function filterFields(Request $request): array
    {
        return [
            'route.context_name' => 'context_name',
            'route.inetCidrRouteProto' => 'proto',
        ];
    }

    protected function sortFields(Request $request): array
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
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Route::class);

        $join = function ($query): void {
            $query->on('ports.port_id', 'route.port_id');
        };
        $showAllRoutes = trim(\Request::input('showAllRoutes'));
        $showProtocols = trim(\Request::input('showProtocols'));
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

    protected function search(?string $search, Builder $query, array $fields): Builder
    {
        if ($search = trim(\Request::input('searchPhrase'))) {
            $searchLike = '%' . $search . '%';

            return $query->where(fn ($query) => $query->where('route.inetCidrRouteNextHop', 'like', $searchLike)
                ->orWhere('route.inetCidrRouteDest', 'like', $searchLike));
        }

        return $query;
    }

    public function sort(Request $request, Builder $query): Builder
    {
        $sort = $request->input('sort');
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
     * @param  Route  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $item = [
            'updated_at' => $model->updated_at ? $model->updated_at->diffForHumans() : $model->updated_at,
            'created_at' => $model->created_at ? $model->created_at->toDateTimeString() : $model->created_at,
            'inetCidrRouteIfIndex' => $model->inetCidrRouteIfIndex == 0 ? 'Undefined' : $model->inetCidrRouteIfIndex,
            'inetCidrRouteMetric1' => $model->inetCidrRouteMetric1,
            'inetCidrRoutePfxLen' => $model->inetCidrRoutePfxLen,
            'inetCidrRouteDestType' => $model->inetCidrRouteDestType,
        ];

        try {
            $obj_inetCidrRouteDest = IP::parse($model->inetCidrRouteDest);
            $item['inetCidrRouteDest'] = $obj_inetCidrRouteDest->compressed();
        } catch (\Exception) {
            $item['inetCidrRouteDest'] = $model->inetCidrRouteDest;
        }

        $item['inetCidrRouteIfIndex'] = $model->inetCidrRouteIfIndex == 0 ? 'Undefined' : 'IfIndex ' . $model->inetCidrRouteIfIndex;
        if ($port = $model->port()->first()) {
            $item['inetCidrRouteIfIndex'] = Blade::render('<x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link>', ['port' => $port]);
        }

        try {
            $obj_inetCidrRouteNextHop = IP::parse($model->inetCidrRouteNextHop);
            $item['inetCidrRouteNextHop'] = $obj_inetCidrRouteNextHop->compressed();
        } catch (\Exception) {
            $item['inetCidrRouteNextHop'] = $model->inetCidrRouteNextHop;
        }
        $device = Device::findByIp($model->inetCidrRouteNextHop);
        if ($device) {
            if ($device->device_id == $model->device_id || in_array($model->inetCidrRouteNextHop, ['127.0.0.1', '::1'])) {
                $item['inetCidrRouteNextHop'] = Blade::render('<x-device-link :device="$device">localhost</x-device-link>', ['device' => $device]);
            } else {
                $item['inetCidrRouteNextHop'] = $item['inetCidrRouteNextHop'] . '<br>(' . rtrim(Blade::render('<x-device-link :device="$device"/>', ['device' => $device])) . ')';
            }
        }

        $item['inetCidrRouteProto'] = $model->inetCidrRouteProto;
        if ($model->inetCidrRouteProto && $model::$translateProto[$model->inetCidrRouteProto]) {
            $item['inetCidrRouteProto'] = $model::$translateProto[$model->inetCidrRouteProto];
        }

        $item['inetCidrRouteType'] = $model->inetCidrRouteType;
        if ($model->inetCidrRouteType && $model::$translateType[$model->inetCidrRouteType]) {
            $item['inetCidrRouteType'] = $model::$translateType[$model->inetCidrRouteType];
        }

        $item['context_name'] = '[global]';
        if ($model->context_name != '') {
            $item['context_name'] = '<a href="' . Url::generate(['page' => 'routing', 'protocol' => 'vrf', 'vrf' => $model->context_name]) . '">' . htmlspecialchars((string) $model->context_name) . '</a>';
        }

        return $item;
    }
}
