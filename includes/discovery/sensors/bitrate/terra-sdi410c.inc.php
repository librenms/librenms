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

if (is_array($pre_cache['sdi410cstatus'])) {
    d_echo('Terra sdi410C Bitrates');
    for ($streamid = 1; $streamid <= 25; $streamid++) {
        $br = $pre_cache['sdi410cstatus'][0]['outBr' . $streamid];
        if ($br) {
            $oid = '.1.3.6.1.4.1.30631.1.8.1.' . (1 + $streamid) . '.1.0';
            $type = 'terra_brout';
            $descr = 'Out# ' . sprintf('%02d', $streamid);
            $limit = 50 * 1000 * 1000; // 50 mbit/s
            $limitwarn = 49 * 1000 * 1000; // 49 mbit/s
            $lowwarnlimit = 1 * 1000 * 1000; // 1 mbit/s
            $lowlimit = 100 * 1000; // 100 kbit/s
            $value = $br * $multiplier;
            $group = 'Streams';
            discover_sensor(
                null,
                'bitrate',
                $device,
                $oid,
                $streamid,
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
