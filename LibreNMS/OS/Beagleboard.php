<?php

/**
 * Beagleboard.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use SnmpQuery;

class Beagleboard extends Linux implements OSDiscovery
{
    /**
     * Retrieve basic information about the OS / device
     */
    public function discoverOS(Device $device): void
    {
        $oids = ['NET-SNMP-EXTEND-MIB::nsExtendOutputFull."distro"', 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."hardware"'];
        $info = snmp_get_multi($this->getDeviceArray(), $oids);
        $device->version = str_replace('BeagleBoard.org ', '', $info['"distro"']['nsExtendOutputFull']);
        $device->hardware = $info['"hardware"']['nsExtendOutputFull'];
    }

    /**
     * The beagleboard extend outputs CPU, GPU, Core, DSP and IVA-HD temperatures in millidegrees
     */
    protected function discoverOsSensors(): Collection
    {
        $sensors = parent::discoverOsSensors();
        $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.11.98.101.97.103.108.101.98.111.97.114.100';
        $values = array_values(SnmpQuery::numeric()->walk($oid)->values());

        foreach (['CPU', 'GPU', 'Core', 'DSP', 'IVA-HD'] as $index => $descr) {
            $value = trim((string) ($values[$index] ?? ''), '"');
            if (! is_numeric($value)) {
                break;
            }

            $sensors->push(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'temperature',
                'sensor_oid' => "$oid." . ($index + 1),
                'sensor_index' => $index,
                'sensor_type' => 'beagleboardTemp',
                'sensor_descr' => $descr,
                'sensor_divisor' => 1000,
                'sensor_multiplier' => 1,
                'sensor_current' => $value / 1000,
                'rrd_type' => 'GAUGE',
            ]));
        }

        return $sensors;
    }
}
