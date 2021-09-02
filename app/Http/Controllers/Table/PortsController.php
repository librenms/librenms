<?php
/*
 * PortsController.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Port;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

class PortsController extends TableController
{
    protected function rules()
    {
        return [
            'device_id' => 'nullable|integer',
            'deleted' => 'boolean',
            'disabled' => 'boolean',
            'errors' => 'nullable|boolean',
            'hostname' => 'nullable|ip_or_hostname',
            'ifAlias' => 'nullable|string',
            'ifType' => 'nullable|string',
            'ifSpeed' => 'nullable|integer',
            'ignore' => 'boolean',
            'location' => 'nullable|integer',
            'port_descr_type' => 'nullable|string',
            'state' => 'nullable|in:up,down,admindown',
        ];
    }

    protected function filterFields($request)
    {
        return [
            'ports.device_id' => 'device_id',
            'location_id' => 'location',
            'ifSpeed',
            'ifType',
            'port_descr_type',
            'ports.disabled' => 'disabled',
            'ports.ignore' => 'ignore',
        ];
    }

    protected function sortFields($request)
    {
        return [
            'hostname',
            'ifIndex',
            'ifDescr',
            'secondsIfLastChange',
            'ifConnectorPresent',
            'ifInErrors_delta',
            'ifOutErrors_delta',
            'ifInOctets_rate',
            'ifOutOctets_rate',
            'ifInUcastPkts_rate',
            'ifOutUcastPkts_rate',
            'ifType',
            'ifAlias',
            'ifMtu',
            'ifSpeed',
        ];
    }

    protected function baseQuery($request)
    {
        $query = Port::hasAccess($request->user())
            ->with('device')
            ->leftJoin('devices', 'ports.device_id', 'devices.device_id')
            ->where('deleted', $request->get('deleted', 0)) // always filter deleted
            ->when($request->get('hostname'), function (Builder $query, $hostname) {
                $query->where(function (Builder $query) use ($hostname) {
                    $query->where('hostname', 'like', "%$hostname%")
                        ->orWhere('sysName', 'like', "%$hostname%");
                });
            })
            ->when($request->get('ifAlias'), function (Builder $query, $ifAlias) {
                return $query->where('ifAlias', 'like', "%$ifAlias%");
            })
            ->when($request->get('errors'), function (Builder $query) {
                $query->where(function (Builder $query) {
                    return $query->where('ifInErrors_delta', '>', 0)
                        ->orWhere('ifOutErrors_delta', '>', 0);
                });
            })
            ->when($request->get('state'), function (Builder $query, $state) {
                switch ($state) {
                    case 'down':
                        return $query->where('ifAdminStatus', 'up')->where('ifOperStatus', 'down');
                    case 'up':
                        return $query->where('ifAdminStatus', 'up')->where('ifOperStatus', 'up');
                    case 'admindown':
                        return $query->where('ifAdminStatus', 'down')->where('ports.ignore', 0);
                    default:
                        return $query;
                }
            });

        $select = [
            'ports.*',
            'hostname',
        ];

        if (array_key_exists('secondsIfLastChange', Arr::wrap($request->get('sort')))) {
            // for sorting
            $select[] = DB::raw('`devices`.`uptime` - `ports`.`ifLastChange` / 100 as secondsIfLastChange');
        }

        return $query->select($select);
    }

    /**
     * @param  \App\Models\Port  $port
     * @return array
     */
    public function formatItem($port)
    {
        $status = $port->ifOperStatus == 'down'
            ? ($port->ifAdminStatus == 'up' ? 'label-danger' : 'label-warning')
            : 'label-success';

        return [
            'status' => $status,
            'device' => Url::deviceLink($port->device),
            'port' => Url::portLink($port),
            'secondsIfLastChange' => ceil($port->device->uptime - ($port->ifLastChange / 100)),
            'ifConnectorPresent' => ($port->ifConnectorPresent == 'true') ? 'yes' : 'no',
            'ifSpeed' => $port->ifSpeed,
            'ifMtu' => $port->ifMtu,
            'ifInOctets_rate' => $port->ifInOctets_rate * 8,
            'ifOutOctets_rate' => $port->ifOutOctets_rate * 8,
            'ifInUcastPkts_rate' => $port->ifInUcastPkts_rate,
            'ifOutUcastPkts_rate' => $port->ifOutUcastPkts_rate,
            'ifInErrors_delta' => $port->poll_period ? Number::formatSi($port->ifInErrors_delta / $port->poll_period, 2, 3, 'EPS') : '',
            'ifOutErrors_delta' => $port->poll_period ? Number::formatSi($port->ifOutErrors_delta / $port->poll_period, 2, 3, 'EPS') : '',
            'ifType' => Rewrite::normalizeIfType($port->ifType),
            'ifAlias' => $port->ifAlias,
            'actions' => (string) view('port.actions', ['port' => $port]),
        ];
    }
}
