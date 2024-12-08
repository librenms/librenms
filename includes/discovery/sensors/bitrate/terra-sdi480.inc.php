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

if (is_array($pre_cache['sdi480status'])) {
    d_echo('Terra sdi480 Bitrates');

    //inputs from SAT
    for ($inputid = 1; $inputid <= 8; $inputid++) {
        $br = $pre_cache['sdi480status'][0]['inbr' . $inputid];
        if ($br) {
            $oid = '.1.3.6.1.4.1.30631.1.17.1.' . $inputid . '.4.0';
            $type = 'terra_brin';
            $descr = 'In# ' . sprintf('%02d', $inputid);
            $value = $br * $multiplier;
            $group = 'Inputs';
            discover_sensor(
                null,
                'bitrate',
                $device,
                $oid,
                $inputid,
                $type,
                $descr,
                $divisor,
                $multiplier,
                $lowlimit,
                $lowwarnlimit,
                $limitwarn,
                $limit,
                $value,
                'snmp',
                null,
                null,
                null,
                $group
            );
        }
    }

    //outputs per stream
    for ($outputid = 1; $outputid <= 576; $outputid++) {
        $br = $pre_cache['sdi480status'][0]['outBr' . $outputid];
        if ($br) {
            $oid = '.1.3.6.1.4.1.30631.1.17.1.' . (9 + $outputid) . '.1.0';
            $type = 'terra_brout';
            $descr = 'Out# ' . sprintf('%03d', $outputid);
            $value = $br * $multiplier;
            $group = 'Streams';
            discover_sensor(
                null,
                'bitrate',
                $device,
                $oid,
                $outputid,
                $type,
                $descr,
                $divisor,
                $multiplier,
                $lowlimit,
                $lowwarnlimit,
                $limitwarn,
                $limit,
                $value,
                'snmp',
                null,
                null,
                null,
                $group
            );
        }
    }
}
