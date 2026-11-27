<?php

/**
 * AristaEos.php
 *
 * Arista EOS
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Sensor;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use Illuminate\Support\Str;
use LibreNMS\Util\UserFuncHelper;
use SnmpQuery;

class AristaEos extends Shared\Arista implements TransceiverDiscovery
{
    /** @var Collection<int, Transceiver>|null */
    private ?Collection $plr4Transceivers = null;

    /**
     * A PLR4 breakout exposes a transceiver lane entity for each logical 100G port.
     * Keep the native transceiver-to-port association instead of interpreting a sensor's port label.
     *
     * @return Collection<int, Transceiver>
     */
    public function discoverTransceivers(): Collection
    {
        if ($this->plr4Transceivers !== null) {
            return $this->plr4Transceivers;
        }

        $entities = SnmpQuery::cache()->hideMib()->enumStrings()->walk('ENTITY-MIB::entPhysicalTable')->table(1);
        $ports = $this->getDevice()->ports()->where('deleted', 0)->get()->keyBy('ifName');
        $transceivers = new Collection;
        foreach ($entities as $moduleIndex => $module) {
            if (! str_contains($module['entPhysicalDescr'] ?? '', '400GBASE-PLR4')
                || ! preg_match('/^Xcvr for (Ethernet[0-9]+):/', $module['entPhysicalName'] ?? '', $match)) {
                continue;
            }

            $parent = $match[1];
            $breakout = $ports->filter(fn ($port) => str_starts_with($port->ifName, $parent . '/'))->sortBy('ifName', SORT_NATURAL)->values();
            if ($ports->has($parent) || $breakout->count() !== 4
                || $breakout->contains(fn ($port) => (int) $port->ifSpeed !== 100000000000)
                || ! in_array($breakout->pluck('ifName')->all(), [
                    array_map(fn ($lane) => "$parent/$lane", [1, 3, 5, 7]),
                    array_map(fn ($lane) => "$parent/$lane", [1, 2, 3, 4]),
                ], true)) {
                continue; // Standalone, 8x50G and ambiguous layouts are not four-port PLR4 breakouts.
            }

            $lanes = collect($entities)->filter(fn ($entity) => ($entity['entPhysicalContainedIn'] ?? null) == $moduleIndex
                && ($entity['entPhysicalClass'] ?? '') === 'module'
                && preg_match('/^Lane [0-9]+ for Xcvr for ' . preg_quote($parent, '/') . '$/', $entity['entPhysicalName'] ?? '')
                && isset($entity['entPhysicalParentRelPos']) && in_array((int) $entity['entPhysicalParentRelPos'], [0, 1, 2, 3], true));
            if ($lanes->count() !== 4 || $lanes->pluck('entPhysicalParentRelPos')->unique()->count() !== 4) {
                continue;
            }

            foreach ($lanes as $index => $lane) {
                $port = $breakout[(int) $lane['entPhysicalParentRelPos']];
                $transceivers->put($index, (new Transceiver([
                    'index' => $index,
                    'entity_physical_index' => $index,
                    'port_id' => $port->port_id,
                    'type' => $module['entPhysicalDescr'],
                    'vendor' => $module['entPhysicalMfgName'] ?? null,
                    'model' => $module['entPhysicalModelName'] ?? null,
                    'serial' => $module['entPhysicalSerialNum'] ?? null,
                    'revision' => $module['entPhysicalHardwareRev'] ?? null,
                    'channels' => 1,
                    'ddm' => true,
                ]))->setRelation('port', $port));
            }
        }

        return $this->plr4Transceivers = $transceivers;
    }

    /**
     * Unnamed entities get a name from their description and the unit they belong to
     */
    protected function entitySensorDescr(int $index, array $entities): string
    {
        $descr = parent::entitySensorDescr($index, $entities);
        if ($descr !== '') {
            return $descr;
        }

        $descr = $entities[$index]['entPhysicalDescr'] ?? '';
        if (preg_match('/(Input|Output) (voltage|current) sensor/i', $descr) || preg_match('/^(Power Supply|Hotspot|Inlet|Board)/i', $descr)) {
            $unit = $entities[substr_replace((string) $index, '000', -3)]['entPhysicalDescr'] ?? '';
            $descr = ucwords($unit) . ' ' . preg_replace('/(Voltage|Current|Power Supply) Sensor$/i', '', ucwords($descr));
        }

        return preg_replace('/(temp|temperature) sensor$/i', '', $descr);
    }

    /**
     * DOM power is converted to dBm, limits come from ARISTA-ENTITY-SENSOR-MIB and sensors are grouped
     */
    protected function entitySensor(Sensor $sensor, array $entry): ?Sensor
    {
        // DOM power is reported in 0.1 µW, polling converts it the same way
        $toDbm = fn ($value) => UserFuncHelper::apply('uw_to_dbm', $value / 10);
        $limitDbm = fn ($value) => is_numeric($dbmValue = $toDbm($value)) ? round($dbmValue, 2) : null;
        $dbm = $sensor->sensor_class == 'power' && preg_match('/DOM [RT]x Power/i', $sensor->sensor_descr);

        if ($dbm) {
            $sensor->fill([
                'sensor_class' => 'dbm',
                'sensor_current' => $toDbm($entry['entPhySensorValue'] ?? 0),
                'sensor_divisor' => 10,
                'sensor_multiplier' => 1,
                'user_func' => 'uw_to_dbm',
            ]);
        }

        $thresholds = SnmpQuery::cache()->hideMib()->walk('ARISTA-ENTITY-SENSOR-MIB::aristaEntSensorThresholdTable')->table(1)[$sensor->sensor_index] ?? [];
        foreach ([
            'sensor_limit_low_warn' => 'aristaEntSensorThresholdLowWarning',
            'sensor_limit_low' => 'aristaEntSensorThresholdLowCritical',
            'sensor_limit_warn' => 'aristaEntSensorThresholdHighWarning',
            'sensor_limit' => 'aristaEntSensorThresholdHighCritical',
        ] as $limit => $oid) {
            $value = $thresholds[$oid] ?? null;
            if (is_numeric($value) && abs($value) != 1000000000) {
                $sensor->$limit = $dbm ? $limitDbm($value) : $value * $sensor->sensor_multiplier / $sensor->sensor_divisor;
            }
        }

        $descr = $sensor->sensor_descr;
        if (preg_match('/DOM /i', $descr)) {
            $sensor->group = 'SFPs';
        } elseif (preg_match('/PwrCon/', $descr)) {
            $unit = explode(' ', $descr)[0];
            $sensor->group = preg_match('/PwrCon[0-9]/', $unit) ? $unit : preg_replace('/PwrCon/i', '', $unit);
            $sensor->sensor_descr = preg_replace('/^.*?(PwrCon)[0-9]*/i', '', $descr);
        } elseif (preg_match('/^(Trident.*|Jericho[0-9]|FM6000)/i', $descr)) {
            $sensor->group = 'Platform';
        } elseif (preg_match('/^(Power|PSU)/i', $descr)) {
            $sensor->group = 'PSUs';
        } else {
            $sensor->group = 'System';
            $sensor->sensor_descr = Str::replaceLast('temp sensor', '', $descr);
        }

        if (preg_match('/^DOM (RX|TX) Power\s+For Ethernet[0-9]+\/([1-4])$/i', $sensor->sensor_descr, $match)) {
            $entities = SnmpQuery::cache()->hideMib()->enumStrings()->walk('ENTITY-MIB::entPhysicalTable')->table(1);
            $parent = $entities[$sensor->sensor_index]['entPhysicalContainedIn'] ?? null;
            $transceiver = $this->discoverTransceivers()->get($parent);
            if ($transceiver !== null) {
                $sensor->entPhysicalIndex = (string) $transceiver->entity_physical_index;
                $sensor->group = 'transceiver';
                $sensor->sensor_descr = 'DOM ' . strtoupper($match[1]) . ' Power For ' . $transceiver->port->ifName . ' (optical lane ' . ($entities[$parent]['entPhysicalParentRelPos'] + 1) . ')';
            }
        }

        return $sensor;
    }
}
