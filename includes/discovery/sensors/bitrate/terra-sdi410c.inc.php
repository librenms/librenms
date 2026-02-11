<?php

/*
 * LibreNMS discovery module for Terra-sdi410c bitrates
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
 * @copyright  2022 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$divisor = 1;
$multiplier = 1000;

$oids = SnmpQuery::hideMib()->walk('TERRA-sdi410C-MIB::sdi410cstatus')->table(1);

if (is_array($oids)) {
    d_echo('Terra sdi410C Bitrates');
    for ($streamid = 1; $streamid <= 25; $streamid++) {
        $br = $oids[0]['outBr' . $streamid];
        if ($br) {
            $oid = '.1.3.6.1.4.1.30631.1.8.1.' . (1 + $streamid) . '.1.0';
            $type = 'terra_brout';
            $descr = 'Out# ' . sprintf('%02d', $streamid);
            $limit = 50 * 1000 * 1000; // 50 mbit/s
            $limitwarn = 49 * 1000 * 1000; // 49 mbit/s
            $lowwarnlimit = 1 * 1000 * 1000; // 1 mbit/s
            $lowlimit = 100 * 1000; // 100 kbit/s
            $value = $br * $multiplier;

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'bitrate',
                'sensor_oid' => $oid,
                'sensor_index' => $streamid,
                'sensor_type' => $type,
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_limit_low' => $lowlimit,
                'sensor_limit_low_warn' => $lowwarnlimit,
                'sensor_limit_warn' => $limitwarn,
                'sensor_limit' => $limit,
                'sensor_current' => $value,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => 'Streams',
            ]));
        }
    }
}
