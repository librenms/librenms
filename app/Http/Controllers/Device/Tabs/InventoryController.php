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
use App\Models\Sensor;
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

        return match ($this->getType($device)) {
            'entphysical' => [
                'type' => 'entphysical',
                'tree' => $this->getEntPhysicalTree($device),
            ],
            'hrdevice' => [
                'type' => 'hrdevice',
                'items' => $this->getHrDeviceItems($device),
            ],
            default => [
                'type' => '',
                'items' => [],
                'tree' => [],
            ],
        };
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

        $allIndices = $entities->pluck('entPhysicalIndex')->flip();
        $roots = $entities->filter(fn (EntPhysical $ent) => $ent->entPhysicalContainedIn == 0 || ! $allIndices->has($ent->entPhysicalContainedIn));

        return $roots->map(fn (EntPhysical $root) => $this->buildNode($root, $grouped, $entityStates, $ports, $sensors, $device))->values()->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int|string, \Illuminate\Database\Eloquent\Collection<int, EntPhysical>>  $grouped
     * @param  \Illuminate\Database\Eloquent\Collection<int, EntityState>  $entityStates
     * @param  \Illuminate\Database\Eloquent\Collection<int, Port>  $ports
     * @param  \Illuminate\Database\Eloquent\Collection<int, Sensor>  $sensors
     * @return array<string, mixed>
     */
    private function buildNode(
        EntPhysical $ent,
        \Illuminate\Support\Collection $grouped,
        \Illuminate\Database\Eloquent\Collection $entityStates,
        \Illuminate\Database\Eloquent\Collection $ports,
        \Illuminate\Database\Eloquent\Collection $sensors,
        Device $device
    ): array {
        $entSensors = $sensors->filter(fn (Sensor $s) => $s->entPhysicalIndex == $ent->entPhysicalIndex || $s->sensor_index == $ent->entPhysicalIndex);
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

        $sensorData = [];
        foreach ($entSensors as $sensor) {
            $cleaned = trim(str_replace([$ent->entPhysicalDescr, $ent->entPhysicalName], ['', ''], (string) $sensor->sensor_descr));
            $description = trim(($cleaned ?: $sensor->sensor_descr) . ' ' . $sensor->sensor_class);

            $sensorData[] = [
                'sensor' => $sensor,
                'description' => $description,
                'status' => $sensor->currentStatus(),
                'value' => $sensor->formatValue(),
                'graph_url' => route('graphs', ['type' => 'sensor_' . $sensor->sensor_class, 'id' => $sensor->sensor_id]),
                'graph_type' => 'sensor_' . $sensor->sensor_class,
                'graph_vars' => ['id' => $sensor->sensor_id],
                'popup_title' => $device->display ? $device->display . ' - ' . $sensor->sensor_descr . ' ' . $sensor->sensor_class : $sensor->sensor_descr . ' ' . $sensor->sensor_class,
            ];
        }

        $children = ($grouped->get($ent->entPhysicalIndex) ?? collect())
            ->map(fn (EntPhysical $child) => $this->buildNode($child, $grouped, $entityStates, $ports, $sensors, $device))
            ->all();

        return [
            'entity' => $ent,
            'label' => $this->resolveEntityLabel($ent),
            'icon' => $this->resolveEntityIcon($ent),
            'port' => $port,
            'sensors' => $sensorData,
            'states' => $states,
            'alarms' => $alarms,
            'children' => $children,
        ];
    }

    private function resolveEntityLabel(EntPhysical $ent): ?string
    {
        $displayName = $ent->entPhysicalName;

        if ($ent->entPhysicalModelName) {
            return $ent->entPhysicalModelName;
        }
        if (is_numeric($displayName) && $ent->entPhysicalVendorType) {
            return $displayName . ' ' . $ent->entPhysicalVendorType;
        }
        if ($displayName) {
            return $displayName;
        }
        if ($ent->entPhysicalDescr) {
            return $ent->entPhysicalDescr;
        }
        if ($ent->entPhysicalClass) {
            return $ent->entPhysicalClass;
        }

        return null;
    }

    private function resolveEntityIcon(EntPhysical $ent): string
    {
        return match ($ent->entPhysicalClass) {
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
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getHrDeviceItems(Device $device): array
    {
        $hrDevices = $device->hostResources()->orderBy('hrDeviceIndex')->get();
        $processors = $device->processors()->get()->keyBy('hrDeviceIndex');
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
                'hr' => $hr,
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
