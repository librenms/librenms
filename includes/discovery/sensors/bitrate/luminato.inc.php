<?php
/*
 * LibreNMS discovery module for Teleste Luminato ASI/QAM bitrate
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
 * @copyright  2021 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$divisor = 1;
$ver = intval($device['version']);
$boid = '.1.3.6.1.4.1.3715.17.3.3.1.';

if (is_array($pre_cache['transfer'])) {
    d_echo('Luminato transfer' . $ver);
    foreach ($pre_cache['transfer'] as $key => $data) {
        if ($data['ifExtDirection'] == 'output') {
            if ($ver < 10) { //v10 and up auto reset transBitrateMax, older version could not use this sensor
                $value = $data['transBitrate'] / $divisor;
                $oid = $boid . '2.' . $key;
            } else {
                $value = $data['transBitrateMax'] / $divisor;
                $oid = $boid . '4.' . $key;
            }

            unset($defrate);
            $mnr = $pre_cache['transfer'][$key]['ifExtModule']; //module nr
            $mname = $pre_cache['entPhysicalDescr'][$mnr]['entPhysicalDescr']; //module name
            switch ($mname) {
                case 'LAS-D':		// AsiOut
                case 'LRT-C':		// DVB-T/T2
                case 'LCM-B':		// DVB-T
                case 'LRS-D':		// DVB-S2
                case 'LCM-B':		// DVB-T
                    $defrate = 60;
                    break;
                case 'LQM-C':		// QAM module
                case 'LDM-C':		// QAM module
                default:
                    $defrate = 50;
                    break;
            }
            if (isset($defrate)) {
                $type = 'Transfer_' . $mname;
                $defrate = $defrate * 1000 * 1000;
                $descr = $mname . ' output ';
                $descr .= sprintf('%02d', $pre_cache['transfer'][$key]['ifExtModule']) . '.'; //include module nr
                $descr .= sprintf('%02d', $pre_cache['transfer'][$key]['ifExtPhysInterface']) . '.';
                $descr .= sprintf('%02d', $pre_cache['transfer'][$key]['ifExtLogiInterface']);
                $group = 'Slot ' . $mnr;
                $limit = $defrate * 1; //100%
                $limitwarn = $defrate * 0.8; //80%
                $lowlimit = 0;
                $lowwarnlimit = $defrate * 0.1; //10%
                discover_sensor($valid['sensor'], 'bitrate', $device, $oid, $key, $type, $descr, $divisor, 1, $lowlimit, $lowwarnlimit, $limitwarn, $limit, $value, 'snmp', null, null, null, $group);
            }
        }
    }
}
