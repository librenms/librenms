<?php

/*
 * LibreNMS discovery module for Terra-sdi480 bitrates
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
$multiplier = 100 * 1000;
$limit = 60 * 1000 * 1000;  // 50 mbps
$limitwarn = 49 * 1000 * 1000; // 49 mbps
$lowwarnlimit = 1 * 1000 * 1000; // 1 mbps
$lowlimit = 100 * 1000; // 0.1 mbps

$oids = SnmpQuery::cache()->hideMib()->walk('TERRA-sdi480-MIB::sdi480status')->table(1);

if (is_array($oids)) {
    d_echo('Terra sdi480 Bitrates');

    //inputs from SAT
    for ($inputid = 1; $inputid <= 8; $inputid++) {
        $br = $oids[0]['inbr' . $inputid];
        if ($br) {
            $oid = '.1.3.6.1.4.1.30631.1.17.1.' . $inputid . '.4.0';
            $type = 'terra_brin';
            $descr = 'In# ' . sprintf('%02d', $inputid);
            $value = $br * $multiplier;

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'bitrate',
                'sensor_oid' => $oid,
                'sensor_index' => $inputid,
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
                'group' => 'Inputs',
            ]));
        }
    }

    //outputs per stream
    for ($outputid = 1; $outputid <= 576; $outputid++) {
        $br = $oids[0]['outBr' . $outputid];
        if ($br) {
            $oid = '.1.3.6.1.4.1.30631.1.17.1.' . (9 + $outputid) . '.1.0';
            $type = 'terra_brout';
            $descr = 'Out# ' . sprintf('%03d', $outputid);
            $value = $br * $multiplier;

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'bitrate',
                'sensor_oid' => $oid,
                'sensor_index' => $outputid,
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
