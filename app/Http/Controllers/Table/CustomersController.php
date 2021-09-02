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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Port;
use Illuminate\Support\Arr;
use LibreNMS\Config;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

class CustomersController extends TableController
{
    public function searchFields($request)
    {
        return ['port_descr_descr', 'ifName', 'ifDescr', 'ifAlias', 'hostname', 'sysDescr', 'port_descr_speed', 'port_descr_notes'];
    }

    public function sortFields($request)
    {
        return ['port_descr_descr', 'hostname', 'ifDescr', 'port_descr_speed', 'port_descr_circuit', 'port_descr_notes'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
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
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($paginator)
    {
        $customers = collect($paginator->items())->pluck('port_descr_descr');
        // fetch all ports
        $ports = Port::whereIn('port_descr_descr', $customers)
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
     * @param Port $port
     * @return array|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function formatItem($port)
    {
        return [
            'port_descr_descr'   => $port->port_descr_descr,
            'hostname'          => Url::deviceLink($port->device),
            'ifDescr'            => Url::portLink($port),
            'port_descr_speed'   => $port->port_descr_speed,
            'port_descr_circuit' => $port->port_descr_circuit,
            'port_descr_notes'   => $port->port_descr_notes,
        ];
    }

    private function getGraphRow($customer)
    {
        $graph_array = [
            'type' => 'customer_bits',
            'height' => 100,
            'width' => 220,
            'id' => $customer,
        ];

        $graph_data = Html::graphRow($graph_array);

        return [
            'port_descr_descr'   => $graph_data[0],
            'hostname'          => $graph_data[1],
            'ifDescr'            => '',
            'port_descr_speed'   => '',
            'port_descr_circuit' => $graph_data[2],
            'port_descr_notes'   => $graph_data[3],
        ];
    }

    private function getTypeStrings()
    {
        return Arr::wrap(Config::get('customers_descr', ['cust']));
    }
}
