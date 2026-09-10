<?php

/*
 * LibreNMS discovery module for Eltex-MES23xx PoE power
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

$oids = SnmpQuery::hideMib()->walk([
    'MARVELL-POE-MIB::rlPethPsePortPowerLimit',
    'MARVELL-POE-MIB::rlPethPsePortOutputPower',
])->table(2);
$divisor = 1000;

foreach ($oids as $unit => $indexData) {
    foreach ($indexData as $ifIndex => $data) {
        if (isset($data['rlPethPsePortOutputPower'])) {
            $value = $data['rlPethPsePortOutputPower'] / $divisor;
            if ($value) {
                $port = PortCache::getByIfIndex($ifIndex, $device['device_id']);
                $descr = $port?->ifName;
                $index = $unit . '.' . $ifIndex;
                $oid = '.1.3.6.1.4.1.89.108.1.1.5.' . $index;

                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'power',
                    'sensor_oid' => $oid,
                    'sensor_index' => 'Poe' . $index,
                    'sensor_type' => 'rlPethPsePortOutputPower',
                    'sensor_descr' => 'PoE-' . $descr,
                    'sensor_divisor' => $divisor,
                    'sensor_multiplier' => 1,
                    'sensor_limit_low' => 0,
                    'sensor_limit_low_warn' => 0,
                    'sensor_limit_warn' => isset($data['rlPethPsePortPowerLimit']) ? ($data['rlPethPsePortPowerLimit'] / $divisor) * 0.8 : null,
                    'sensor_limit' => isset($data['rlPethPsePortPowerLimit']) ? ($data['rlPethPsePortPowerLimit'] / $divisor) : null,
                    'sensor_current' => $value,
                    'entPhysicalIndex' => null,
                    'entPhysicalIndex_measured' => null,
                    'user_func' => null,
                    'group' => 'PoE',
                ]));
            }
        }
    }
}
