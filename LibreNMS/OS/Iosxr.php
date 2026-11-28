<?php

/**
 * Iosxr.php
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

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Util\UserFuncHelper;

class Iosxr extends Shared\Cisco implements OSDiscovery
{
    /**
     * Optical power is reported in watts, add dBm sensors for it.
     */
    public function discoverCiscoEntitySensors(): Collection
    {
        $sensors = parent::discoverCiscoEntitySensors();

        return $sensors->merge($sensors
            ->filter(fn (Sensor $sensor) => $sensor->sensor_class == 'power' && preg_match('/power [RT]x|[RT]x (Power|Lane)/i', $sensor->sensor_descr))
            ->map(function (Sensor $sensor) {
                $dbm = $sensor->replicate()->fill([
                    'sensor_class' => 'dbm',
                    'sensor_multiplier' => $sensor->sensor_multiplier * 1000,
                    'user_func' => 'mw_to_dbm',
                    'sensor_current' => UserFuncHelper::apply('mw_to_dbm', $sensor->sensor_current * 1000),
                ]);

                foreach (Sensor::LIMITS as $limit) {
                    $value = $sensor->$limit === null ? null : UserFuncHelper::apply('mw_to_dbm', $sensor->$limit * 1000);
                    $dbm->$limit = $value === null ? null : round($value, 3);
                }

                return $dbm;
            }));
    }

    /**
     * Sensor entities are always named, descriptions are not used.
     */
    protected function ciscoEntitySensorName(int $index, array $entities): string
    {
        return $entities[$index]['ENTITY-MIB::entPhysicalName'] ?? '';
    }

    public function discoverOS(Device $device): void
    {
        $device->serial = $this->getMainSerial();

        if (preg_match('/^Cisco IOS XR Software \(Cisco ([^\)]+)\),\s+Version ([^\[]+)\[([^\]]+)\]/', $device->sysDescr, $regexp_result)) {
            $device->hardware = $regexp_result[1];
            $device->features = $regexp_result[3];
            $device->version = $regexp_result[2];
        } elseif (preg_match('/^Cisco IOS XR Software \(([^\)]+)\),\s+Version\s+([^\s]+)/', $device->sysDescr, $regexp_result)) {
            $device->hardware = $regexp_result[1];
            $device->version = $regexp_result[2];
        }

        $oids = ['entPhysicalSoftwareRev.1', 'entPhysicalModelName.8384513', 'entPhysicalModelName.8384518'];
        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', 'ENTITY-MIB');

        if (! empty($data[1]['entPhysicalSoftwareRev'])) {
            $device->version = $data[1]['entPhysicalSoftwareRev'];
        }

        if (! empty($data[8384513]['entPhysicalModelName'])) {
            $device->hardware = $data[8384513]['entPhysicalModelName'];
        } elseif (! empty($data[8384518]['entPhysicalModelName'])) {
            $device->hardware = $data[8384518]['entPhysicalModelName'];
        }
    }
}
