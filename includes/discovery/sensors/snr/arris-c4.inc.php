<?php

/**
 * arris-c4.inc.php
 *
 * LibreNMS snr discovery module for Arris CMTS
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
 * @copyright  2018 TheGreatDoc
 * @author     TheGreatDoc
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * Based on Neil Lathwood Cisco EPC files
 */

//pre-cache
$oids = SnmpQuery::walk([
    'DOCS-IF-MIB::docsIfSignalQualityTable',
])->table(1);

foreach ($oids as $index => $data) {
    if (is_numeric($data['DOCS-IF-MIB::docsIfSigQSignalNoise'])) {
        $port = PortCache::getByIfIndex($index, $device['device_id']);
        $descr = 'Channel ' . $port?->ifAlias . ' - ' . $port?->ifName;
        $oid = '.1.3.6.1.2.1.10.127.1.1.4.1.5.' . $index;
        $divisor = 10;
        $value = $data['DOCS-IF-MIB::docsIfSigQSignalNoise'];
        if (preg_match('/.0$/', $port?->ifName)) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'snr',
                'sensor_oid' => $oid,
                'sensor_index' => 'docsIfSigQSignalNoise.' . $index,
                'sensor_type' => 'cmts',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => null,
                'sensor_current' => $value,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => null,
            ]));
        }
    }
}
