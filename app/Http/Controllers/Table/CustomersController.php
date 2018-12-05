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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Port;
use LibreNMS\Config;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

class CustomersController extends TableController
{
    public function searchFields($request)
    {
        return ['port_descr_descr', 'ifName', 'ifDescr', 'ifAlias', 'hostname', 'sysDescr', 'port_descr_speed', 'port_descr_notes'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        $cust_descrs = (array)Config::get('customers_descr', ['cust']);
        $fields = [
            'port_descr_descr',
            'port_id',
            'ports.device_id',
            'port_descr_circuit',
            'port_descr_speed',
            'port_descr_notes',
            'ifDescr',
            'ifName',
            'ifIndex',
            'ifOperStatus',
            'ifAdminStatus',
            'ifAlias',
            'ifVlan',
            'ifTrunk',
            // devices for search
            'hostname',
            'sysDescr'
        ];

//        return Port::hasAccess($request->user())
        return Port::query()
            ->with('device')
            ->leftJoin('devices', 'ports.device_id', 'devices.device_id')
            ->select($fields)
            ->whereIn('port_descr_type', $cust_descrs)
            ->groupBy($fields);
    }

    /**
     * @param Port $port
     * @return array|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function formatItem($port)
    {
//        $item = $port->all(['port_descr_descr', 'port_descr_speed', 'port_descr_circuit', 'port_descr_notes']);

        return [
            'port_descr_descr' => $port->port_descr_descr,
            'device_id' => Url::deviceLink($port->device),
            'ifDescr' => Url::portLink($port),
            'port_descr_speed' => $port->port_descr_speed,
            'port_descr_circuit' => $port->port_descr_circuit,
            'port_descr_notes' => $port->port_descr_notes,
        ];
    }

    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($paginator)
    {
        $items = collect();
        foreach ($paginator->items() as $item) {
            $items->push($this->formatItem($item));
            $items->push($this->getGraphRow($item));
        }

        return response()->json([
            'current' => $paginator->currentPage(),
            'rowCount' => $paginator->count(),
            'rows' => $items,
            'total' => $paginator->total(),
        ]);
    }

    private function getGraphRow($port)
    {
        $graph_array = [
            'type' => 'customer_bits',
            'height' => 100,
            'width' => 220,
            'id' => $port->port_descr_descr,
        ];

        $graph_data = Html::graphRow($graph_array);

        return [
            'port_descr_descr'   => $graph_data[0],
            'device_id'          => $graph_data[1],
            'ifDescr'            => '',
            'port_descr_speed'   => '',
            'port_descr_circuit' => $graph_data[2],
            'port_descr_notes'   => $graph_data[3],
        ];
    }
}
