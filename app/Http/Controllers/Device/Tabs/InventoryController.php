<?php

/**
 * InventoryController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;

class InventoryController implements DeviceTab
{
    private $type = null;
    private bool $detail = false;
    private array $settings = [];
    private array $defaults = [
        'perPage' => 'all',
        'sort' => 'ifIndex',
        'order' => 'asc',
        'disabled' => true,
        'ignored' => true,
        'admin' => 'any',
        'status' => 'any',
    ];

    public function __construct()
    {
        if (Config::get('enable_inventory')) {
            $device = DeviceCache::getPrimary();

            if ($device->entityPhysical()->exists()) {
                $this->type = 'entphysical';
            } elseif ($device->hostResources()->exists()) {
                $this->type = 'hrdevice';
            }
        }
    }

    public function visible(Device $device): bool
    {
        return $this->type !== null;
    }

    public function slug(): string
    {
        return 'inventory';
    }

    public function icon(): string
    {
        return 'fa-cube';
    }

    public function name(): string
    {
        return __('Inventory');
    }

    public function data(Device $device, Request $request): array
    {
        /*
        return [
            'tab' => $this->type, // inject to load correct legacy file
        ];
        */
        $this->loadSettings($request);
        $data = $this->portData($device, $request);

        return array_merge([
            'tab' => $this->type, // inject to load correct legacy file
        ], $data);
    }

    private function loadSettings(Request $request): void
    {
        $this->settings = $this->defaults;
    }

    private function getFilteredPortsQuery(Device $device, array $relationships = []): Builder
    {
        $orderBy = match ($this->settings['sort']) {
            'traffic' => \DB::raw('ports.ifInOctets_rate + ports.ifOutOctets_rate'),
            'speed' => 'ifSpeed',
            'media' => 'ifType',
            'mac' => 'ifPhysAddress',
            'port' => 'ifName',
            default => 'ifIndex',
        };

        return Port::where('device_id', $device->device_id)
            ->isNotDeleted()
            ->hasAccess(Auth::user())->with($relationships)
            ->orderBy($orderBy, $this->settings['order']);
    }

    private function portData(Device $device, Request $request): array
    {
        $relationships = ['groups', 'ipv4', 'ipv6', 'vlans', 'adsl', 'vdsl'];
        if ($this->detail) {
            $relationships[] = 'transceivers';
            $relationships['stackParent'] = fn ($q) => $q->select('port_id');
            $relationships['stackChildren'] = fn ($q) => $q->select('port_id');
        }

        /** @var Collection<Port>|LengthAwarePaginator<Port> $ports */
        $ports = $this->getFilteredPortsQuery($device, $relationships)
            ->paginate(fn ($total) => $this->settings['perPage'] == 'all' ? $total : (int) $this->settings['perPage']) // @phpstan-ignore-line missing closure type
            ->appends('perPage', $this->settings['perPage']);

        $data = [
            'ports' => $ports,
            'graphs' => [
                'bits' => [['type' => 'port_bits', 'title' => trans('Traffic'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                'upkts' => [['type' => 'port_upkts', 'title' => trans('Packets (Unicast)'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                'errors' => [['type' => 'port_errors', 'title' => trans('Errors'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
            ],
        ];

        return $data;
    }
}
