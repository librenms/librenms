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
 * @copyright  2025 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use Illuminate\Support\Facades\Log;

$oids = SnmpQuery::hideMib()->enumStrings()->walk([
    'TELESTE-LUMINATO-MIB::transferTable',
    'TELESTE-LUMINATO-MIB::ifExtTable',
])->table(1);

$ver = intval($device['version']);
if (! empty($oids)) {
    foreach ($oids as $index => $data) {
        if ($data['ifExtDirection'] == 'output') {
            if ($ver < 10) { //v10 and up auto reset transBitrateMax, older version could not use this sensor
                $value = $data['transBitrate'] ?? 0;
                $oid = '.1.3.6.1.4.1.3715.17.3.3.1.2.' . $index;
            } else {
                $value = $data['transBitrateMax'] ?? 0;
                $oid = '.1.3.6.1.4.1.3715.17.3.3.1.4.' . $index;
            }
            $ifExtModule = $data['ifExtModule'];
            unset($defrate);
            $mnr = $data['ifExtModule']; //module nr
            $mname = $pre_cache['entPhysicalDescr'][$ifExtModule]['entPhysicalDescr'] ?? 'unknown'; //module name
            switch ($mname) {
                case 'LAS-D':   // AsiOut
                case 'LRT-C':   // DVB-T/T2
                case 'LCM-B':   // DVB-T
                case 'LRS-D':   // DVB-S2
                case 'LCM-B':   // DVB-T
                    $defrate = 60;
                    break;
                case 'LQM-C':   // QAM module
                case 'LDM-C':   // QAM module
                    $defrate = 50;
                    break;
                default:
                    $defrate = 50;
                    Log::info('Unknown module type');
                    break;
            }

            $defrate = $defrate * 1000 * 1000;
            $descr = $mname . ' output ';
            $descr .= sprintf('%02d', $data['ifExtModule']) . '.'; //include module nr
            $descr .= sprintf('%02d', $data['ifExtPhysInterface']) . '.';
            $descr .= sprintf('%02d', $data['ifExtLogiInterface']);

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'bitrate',
                'sensor_oid' => $oid,
                'sensor_index' => $index,
                'sensor_type' => 'Transfer_' . $mname,
                'sensor_descr' => $descr,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => 0,
                'sensor_limit_low_warn' => $defrate * 0.1, //10%,
                'sensor_limit_warn' => $defrate * 0.8, //80%
                'sensor_limit' => $defrate * 1, //100%
                'sensor_current' => $value,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => 'Slot ' . $mnr,
            ]));
        }
    }
}
