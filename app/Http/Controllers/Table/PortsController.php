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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Port>
 */
class PortsController extends TableController
{
    protected function rules(): array
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
            ...Port::filterValidationRules(),
        ];
    }

    protected function filterFields(Request $request): array
    {
        return [
            'ports.device_id' => 'device_id',
            'location_id' => 'location',
            'ifSpeed',
            'ifType',
            'port_descr_type',
            'ports.disabled' => 'disabled',
            'ports.ignore' => 'ignore',
            'group' => fn ($query, $group) => $query->whereHas('groups', fn ($query) => $query->where('id', $group)),
            'devicegroup' => fn ($query, $devicegroup) => $query->whereHas('device', fn ($query) => $query->whereHas('groups', fn ($query) => $query->where('id', $devicegroup))),
        ];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'hostname',
            'ifIndex',
            'ifDescr',
            'errors' => DB::raw('ifInErrors_rate + ifOutErrors_rate'),
            'secondsIfLastChange',
            'ifConnectorPresent',
            'ifInErrors',
            'ifOutErrors',
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
            'ifDuplex',
        ];
    }

    protected function baseQuery($request): Builder
    {
        $this->authorize('viewAny', Port::class);

        $query = Port::hasAccess($request->user())
            ->with(['device', 'device.location'])
            ->leftJoin('devices', 'ports.device_id', 'devices.device_id')
            ->when($request->array('filter'), fn ($q, $filter) => $q->applyFilters($filter))
            ->unless($request->has('filter.deleted'), fn ($q) => $q->where('ports.deleted', $request->input('deleted', 0)))
            ->when($request->input('hostname'), function (Builder $query, $hostname): void {
                $query->where(function (Builder $query) use ($hostname): void {
                    $query->where('devices.hostname', 'like', "%$hostname%")
                        ->orWhere('devices.sysName', 'like', "%$hostname%");
                });
            })
            ->when($request->input('ifAlias'), fn (Builder $query, $ifAlias) => $query->where('ports.ifAlias', 'like', "%$ifAlias%"))
            ->when($request->input('errors'), fn (Builder $query) => $query->hasErrors())
            ->when($request->input('state'), fn (Builder $query, $state) => match ($state) {
                'down' => $query->isDown(),
                'up' => $query->isUp(),
                'admindown' => $query->isShutdown(),
                default => $query,
            });

        $select = [
            'ports.*',
            'hostname',
        ];

        if (array_key_exists('secondsIfLastChange', Arr::wrap($request->input('sort')))) {
            // for sorting
            $select[] = DB::raw('`devices`.`uptime` - `ports`.`ifLastChange` / 100 as secondsIfLastChange');
        }

        return $query->select($select);
    }

    /**
     * @param  Port  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $status = $model->ifOperStatus == IfOperStatus::Down
            ? ($model->ifAdminStatus == IfOperStatus::Up ? 'label-danger' : 'label-warning')
            : 'label-success';

        return [
            'status' => $status,
            'device' => Url::modernDeviceLink($model->device),
            'port' => Blade::render('<x-port-link :port="$port"/>', ['port' => $model]),
            'secondsIfLastChange' => ceil($model->device?->uptime - ($model->ifLastChange / 100)),
            'ifConnectorPresent' => ($model->ifConnectorPresent == 'true') ? 'yes' : 'no',
            'ifSpeed' => $model->ifSpeed,
            'ifDuplex' => $model->ifDuplex,
            'ifMtu' => $model->ifMtu,
            'ifInOctets_rate' => $model->ifInOctets_rate * 8,
            'ifOutOctets_rate' => $model->ifOutOctets_rate * 8,
            'ifInUcastPkts_rate' => $model->ifInUcastPkts_rate,
            'ifOutUcastPkts_rate' => $model->ifOutUcastPkts_rate,
            'ifInErrors' => $model->ifInErrors,
            'ifOutErrors' => $model->ifOutErrors,
            'ifInErrors_delta' => $model->poll_period ? Number::formatSi($model->ifInErrors_delta / $model->poll_period, 2, 0, 'EPS') : '',
            'ifOutErrors_delta' => $model->poll_period ? Number::formatSi($model->ifOutErrors_delta / $model->poll_period, 2, 0, 'EPS') : '',
            'ifType' => Rewrite::normalizeIfType($model->ifType),
            'ifAlias' => htmlentities((string) $model->ifAlias),
            'actions' => (string) view('port.actions', ['port' => $model]),
        ];
    }

    /**
     * Get headers for CSV export
     */
    protected function getExportHeaders(): array
    {
        return [
            'Device ID',
            'Hostname',
            'Port',
            'ifIndex',
            'Status',
            'Admin Status',
            'Duplex',
            'Speed',
            'MTU',
            'Type',
            'In Rate (bps)',
            'Out Rate (bps)',
            'In Errors',
            'Out Errors',
            'In Error Rate',
            'Out Error Rate',
            'Description',
            'Last Change',
            'Connector Present',
        ];
    }

    /**
     * Format a row for CSV export
     *
     * @param  Port  $port
     * @return array<scalar>
     */
    protected function formatExportRow(Model $port): array
    {
        $status = $port->ifOperStatus?->value;
        $adminStatus = $port->ifAdminStatus?->value;
        $speed = Number::formatSi($port->ifSpeed);

        return [
            'device_id' => $port->device_id,
            'hostname' => $port->device?->displayName(),
            'port' => $port->ifName ?: $port->ifDescr,
            'ifindex' => $port->ifIndex,
            'status' => $status,
            'admin_status' => $adminStatus,
            'ifDuplex' => $port->ifDuplex,
            'speed' => $speed,
            'mtu' => $port->ifMtu,
            'type' => Rewrite::normalizeIfType($port->ifType),
            'in_rate' => Number::formatBi($port->ifInOctets_rate * 8) . 'bps',
            'out_rate' => Number::formatBi($port->ifOutOctets_rate * 8) . 'bps',
            'in_errors' => $port->ifInErrors,
            'out_errors' => $port->ifOutErrors,
            'in_errors_rate' => $port->poll_period ? Number::formatSi($port->ifInErrors_delta / $port->poll_period, 2, 0, 'EPS') : '',
            'out_errors_rate' => $port->poll_period ? Number::formatSi($port->ifOutErrors_delta / $port->poll_period, 2, 0, 'EPS') : '',
            'description' => $port->ifAlias,
            'last_change' => $port->device ? ($port->device->uptime - ($port->ifLastChange / 100)) : 'N/A',
            'connector_present' => ($port->ifConnectorPresent == 'true') ? 'yes' : 'no',
        ];
    }
}
