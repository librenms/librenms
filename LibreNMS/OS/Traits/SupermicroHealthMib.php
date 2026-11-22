<?php

/**
 * SupermicroHealthMib.php
 *
 * Sensors of Supermicro boards from SUPERMICRO-HEALTH-MIB
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

namespace LibreNMS\OS\Traits;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SensorType;
use SnmpQuery;

trait SupermicroHealthMib
{
    /**
     * Monitored fans, temperatures and voltages of smHealthMonitorTable (SuperDoctor)
     *
     * @return Collection<int, Sensor>
     */
    public function discoverSupermicroSensors(): Collection
    {
        $sensors = new Collection;
        $data = SnmpQuery::mibDir('supermicro')->enumStrings()->walk([
            'SUPERMICRO-HEALTH-MIB::smHealthMonitorName',
            'SUPERMICRO-HEALTH-MIB::smHealthMonitorType',
            'SUPERMICRO-HEALTH-MIB::smHealthMonitorReading',
            'SUPERMICRO-HEALTH-MIB::smHealthMonitorHighLimit',
            'SUPERMICRO-HEALTH-MIB::smHealthMonitorLowLimit',
            'SUPERMICRO-HEALTH-MIB::smHealthMonitorMonitor',
        ])->table(1);

        foreach ($data as $index => $monitor) {
            if (($monitor['SUPERMICRO-HEALTH-MIB::smHealthMonitorMonitor'] ?? null) !== 'true') {
                continue;
            }

            $name = (string) ($monitor['SUPERMICRO-HEALTH-MIB::smHealthMonitorName'] ?? '');
            $reading = $monitor['SUPERMICRO-HEALTH-MIB::smHealthMonitorReading'] ?? null;
            $high = $monitor['SUPERMICRO-HEALTH-MIB::smHealthMonitorHighLimit'] ?? null;
            $low = $monitor['SUPERMICRO-HEALTH-MIB::smHealthMonitorLowLimit'] ?? null;

            // type: 0 fan, 1 voltage, 2 temperature
            [$type, $divisor, $descr, $limits] = match ((string) ($monitor['SUPERMICRO-HEALTH-MIB::smHealthMonitorType'] ?? '')) {
                '0' => [SensorType::Fanspeed, 1, str_replace([' Fan Speed', ' Speed'], '', $name), ['sensor_limit_low' => $low]],
                '1' => [SensorType::Voltage, 1000, trim(str_ireplace('Voltage', '', $name)), ['sensor_limit_low' => $low, 'sensor_limit' => $high]],
                '2' => [SensorType::Temperature, 1, trim(str_ireplace('temperature', '', $name)), ['sensor_limit' => $high]],
                default => [null, 1, '', []],
            };

            if ($type === null) {
                continue;
            }

            $sensors->push(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => $type->value,
                'sensor_oid' => ".1.3.6.1.4.1.10876.2.1.1.1.1.4.$index",
                'sensor_index' => $index,
                'sensor_type' => 'supermicro',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => 1,
                'sensor_current' => is_numeric($reading) ? $reading / $divisor : null,
                ...array_map(fn ($limit) => is_numeric($limit) ? $limit / $divisor : null, $limits),
            ]));
        }

        return $sensors;
    }
}
