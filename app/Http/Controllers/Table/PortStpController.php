<?php

/**
 * StpPortsController.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Facades\DeviceCache;
use App\Models\Port;
use App\Models\PortStp;
use App\Models\Stp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Mac;

/**
 * @extends TableController<PortStp>
 */
class PortStpController extends TableController
{
    public function rules(): array
    {
        return [
            'device_id' => 'int',
            'vlan' => 'int',
        ];
    }

    protected function filterFields(Request $request): array
    {
        return [
            'device_id',
            'vlan' => function ($query, $vlan): void {
                $query->where(function ($query) use ($vlan): void {
                    $query->where('vlan', $vlan)->when($vlan == 1, fn ($query) => $query->orWhereNull('vlan'))->when($vlan === null, fn ($query) => $query->orWhere('vlan', 1));
                });
            },
        ];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'device_id',
            'vlan',
            'port_id',
            'priority',
            'state',
            'enable',
            'pathCost',
            'designatedRoot',
            'designatedCost',
            'designatedBridge',
            'designatedPort',
            'forwardTransitions',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Port::class);

        return PortStp::hasAccess($request->user())
            ->with('port');
    }

    /**
     * @param  PortStp  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $drMac = Mac::parse($model->designatedRoot);
        $dbMac = Mac::parse($model->designatedBridge);

        $dr = DeviceCache::get(Stp::where('bridgeAddress', $model->designatedRoot)->whereNot('bridgeAddress', '000000000000')->value('device_id'));
        $db = DeviceCache::get(Stp::where('bridgeAddress', $model->designatedBridge)->whereNot('bridgeAddress', '')->value('device_id'));

        return [
            'port_id' => Blade::render('<x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link><br /> {{ $port->getDescription() }}', ['port' => $model->port]),
            'vlan' => $model->vlan ?: 1,
            'priority' => $model->priority,
            'state' => $model->state,
            'enable' => $model->enable,
            'pathCost' => $model->pathCost,
            'designatedRoot' => $drMac->readable(),
            'designatedRoot_vendor' => $drMac->vendor(),
            'designatedRoot_device' => Blade::render('<x-device-link :device="$device"/>', ['device' => $dr]),
            'designatedCost' => $model->designatedCost,
            'designatedBridge' => $dbMac->readable(),
            'designatedBridge_vendor' => $dbMac->vendor(),
            'designatedBridge_device' => Blade::render('<x-device-link :device="$device"/>', ['device' => $db]),
            'designatedPort' => $model->designatedPort,
            'forwardTransitions' => $model->forwardTransitions,
        ];
    }
}
