<?php

/**
 * CustomersController.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use Countable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Html;

/**
 * @extends TableController<Port>
 */
class CustomersController extends TableController
{
    public function searchFields(Request $request): array
    {
        return ['port_descr_descr', 'ifName', 'ifDescr', 'ifAlias', 'hostname', 'sysDescr', 'port_descr_speed', 'port_descr_notes'];
    }

    public function sortFields(Request $request): array
    {
        return ['port_descr_descr', 'hostname', 'ifDescr', 'port_descr_speed', 'port_descr_circuit', 'port_descr_notes'];
    }

    /**
     * Defines the base query for this resource
     */
    public function baseQuery(Request $request): Builder
    {
        // selecting just the customer name, will fetch port data later
        return Port::hasAccess($request->user())
            ->with('device')
            ->leftJoin('devices', 'ports.device_id', 'devices.device_id')
            ->select('port_descr_descr')
            ->whereIn('port_descr_type', $this->getTypeStrings())
            ->groupBy('port_descr_descr');
    }

    /**
     * @param  LengthAwarePaginator&Countable  $paginator
     * @return JsonResponse
     */
    protected function formatResponse($paginator): JsonResponse
    {
        $customers = collect($paginator->items())->pluck('port_descr_descr');
        // fetch all ports
        $ports = Port::hasAccess(request()->user())
            ->whereIn('port_descr_descr', $customers)
            ->whereIn('port_descr_type', $this->getTypeStrings())
            ->with('device')
            ->get()
            ->groupBy('port_descr_descr');

        $rows = $customers->reduce(function ($rows, $customer) use ($ports) {
            $graph_row = $this->getGraphRow($customer);
            foreach ($ports->get($customer) as $port) {
                $port->port_descr_descr = $customer;
                $rows->push($this->formatItem($port));
                $customer = ''; // only display customer in the first row
            }

            // add graphs row
            $rows->push($graph_row);

            return $rows;
        }, collect());

        return response()->json([
            'current' => $paginator->currentPage(),
            'rowCount' => $paginator->count(),
            'rows' => $rows,
            'total' => $paginator->total(),
        ]);
    }

    /**
     * @param  Port  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'port_descr_descr' => $model->port_descr_descr,
            'hostname' => Blade::render('<x-device-link :device="$device"/>', ['device' => $model->device]),
            'ifDescr' => Blade::render('<x-port-link :port="$port"/>', ['port' => $model]),
            'port_descr_speed' => $model->port_descr_speed,
            'port_descr_circuit' => $model->port_descr_circuit,
            'port_descr_notes' => $model->port_descr_notes,
        ];
    }

    private function getGraphRow(string $customer): array
    {
        $graph_array = [
            'type' => 'customer_bits',
            'height' => 100,
            'width' => 220,
            'id' => $customer,
        ];

        $graph_data = Html::graphRow($graph_array);

        return [
            'port_descr_descr' => $graph_data[0],
            'hostname' => $graph_data[1],
            'ifDescr' => '',
            'port_descr_speed' => '',
            'port_descr_circuit' => $graph_data[2],
            'port_descr_notes' => $graph_data[3],
        ];
    }

    private function getTypeStrings(): array
    {
        return Arr::wrap(LibrenmsConfig::get('customers_descr', ['cust']));
    }
}
