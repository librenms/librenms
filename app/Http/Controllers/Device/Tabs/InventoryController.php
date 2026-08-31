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

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\EntityState;
use App\Models\EntPhysical;
use App\Models\Port;
use App\Models\Processor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Interfaces\UI\DeviceTab;

class InventoryController implements DeviceTab
{
    private ?string $type = null;

    public function visible(Device $device): bool
    {
        return (bool) $this->getType($device);
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
        Gate::authorize('view', $device);
        abort_if(Gate::none(['inventory.view', 'inventory.viewAll']), 403);

        $type = $this->getType($device);

        if ($type === 'entphysical') {
            return [
                'type' => 'entphysical',
                'tree' => $this->getEntPhysicalTree($device),
            ];
        }

        if ($type === 'hrdevice') {
            return [
                'type' => 'hrdevice',
                'items' => $this->getHrDeviceItems($device),
            ];
        }

        return [
            'type' => '',
            'items' => [],
            'tree' => [],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getEntPhysicalTree(Device $device): array
    {
        $entities = $device->entityPhysical()
            ->orderBy('entPhysicalContainedIn')
            ->orderBy('entPhysicalIndex')
            ->get();

        if ($entities->isEmpty()) {
            return [];
        }

        $entityStates = EntityState::where('device_id', $device->device_id)->get()->keyBy('entPhysical_id');
        $ports = $device->ports()->get()->keyBy('ifIndex');
        $sensors = $device->sensors()->get();

        $grouped = $entities->groupBy('entPhysicalContainedIn');

        $buildNode = function (EntPhysical $ent) use (&$buildNode, $grouped, $entityStates, $ports, $sensors): array {
            $entSensors = $sensors->filter(fn ($s) => $s->entPhysicalIndex == $ent->entPhysicalIndex || $s->sensor_index == $ent->entPhysicalIndex);
            $entState = $entityStates->get($ent->entPhysical_id);
            $port = $ent->ifIndex ? $ports->get($ent->ifIndex) : null;

            $states = [];
            $alarms = [];
            if ($entState) {
                foreach (['entStateOper', 'entStateUsage', 'entStateStandby'] as $stateName) {
                    $val = $entState->{$stateName};
                    if ($val !== null && $val !== '') {
                        $states[] = array_merge(['name' => $stateName, 'value' => $val], parse_entity_state($stateName, $val));
                    }
                }

                if ($entState->entStateAlarm && ! in_array($entState->entStateAlarm, ['00', '80'], true)) {
                    $alarms = parse_entity_state_alarm($entState->entStateAlarm);
                }
            }

            $children = ($grouped->get($ent->entPhysicalIndex) ?? collect())->map(fn ($child) => $buildNode($child))->all();

            $iconClass = match ($ent->entPhysicalClass) {
                'chassis' => 'fa-server',
                'module' => 'fa-database',
                'port' => 'fa-link',
                'container' => 'fa-square',
                'sensor' => 'fa-heartbeat',
                'backplane' => 'fa-bars',
                'stack' => 'fa-list-ol',
                'powerSupply' => 'fa-bolt',
                default => 'fa-cube',
            };

            return [
                'entity' => $ent,
                'icon' => $iconClass,
                'port' => $port,
                'sensors' => $entSensors,
                'states' => $states,
                'alarms' => $alarms,
                'children' => $children,
            ];
        };

        $allIndices = $entities->pluck('entPhysicalIndex')->flip();
        $roots = $entities->filter(function ($ent) use ($allIndices) {
            return $ent->entPhysicalContainedIn == 0 || ! $allIndices->has($ent->entPhysicalContainedIn);
        });

        return $roots->map(fn ($root) => $buildNode($root))->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getHrDeviceItems(Device $device): array
    {
        $hrDevices = $device->hostResources()->orderBy('hrDeviceIndex')->get();
        $processors = Processor::where('device_id', $device->device_id)->get()->keyBy('hrDeviceIndex');
        $ports = $device->ports()->get();

        return $hrDevices->map(function ($hr) use ($processors, $ports) {
            $processor = null;
            $port = null;
            $interfaceText = null;

            if ($hr->hrDeviceType === 'hrDeviceProcessor') {
                $processor = $processors->get($hr->hrDeviceIndex);
            } elseif ($hr->hrDeviceType === 'hrDeviceNetwork') {
                $int = str_replace('network interface ', '', (string) $hr->hrDeviceDescr);
                $port = $ports->first(fn (Port $p) => $p->ifDescr === $int || $p->ifName === $int);
                if ($port) {
                    $interfaceText = $port->port_descr_type ? $port->port_descr_type . ' (' . $int . ')' : $int;
                }
            }

            return [
                'device' => $hr,
                'processor' => $processor,
                'port' => $port,
                'interface_text' => $interfaceText,
            ];
        })->all();
    }

    private function getType(Device $device): string
    {
        if ($this->type === null) {
            $this->type = '';

            if (LibrenmsConfig::get('enable_inventory', true) && Gate::any(['inventory.view', 'inventory.viewAll'])) {
                if ($device->entityPhysical()->exists()) {
                    $this->type = 'entphysical';
                } elseif ($device->hostResources()->exists()) {
                    $this->type = 'hrdevice';
                }
            }
        }

        return $this->type;
    }
}
