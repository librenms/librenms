<?php
/**
 * fs-nmu.inc.php
 * 
 * OAP OEO and EDFA Modules for FibreSwitches NMUs
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
 * 
 * @link       https://www.librenms.org
 * 
 * @copyright  2022 Priority Colo Inc.
 * @author     Jonathan J Davis <davis@1m.ca>
 */

$oap_current_divisor = 100000;
$oap_current_multiplier = 1;
$oap_flags = '-Ovqe';

echo "FS NMU EDFAs current\n";

// OAP C1 -> C16 EDFAs
$oap_edfas = range(1,16);
$oap_edfa_sensors = [
    'PUMPCurrent' => [
        'desc' => 'Pump Current', 
        'id' => '26'
        ],
    'TECCurrent' => [
        'desc' => 'TEC Current',
        'id' => '27'
        ],
    ];

foreach($oap_edfas as $oap_edfa) {
    $object_ident = 'OAP-C' . $oap_edfa . '-EDFA';

    foreach($oap_edfa_sensors as $sensor => $options) {
        $object_type = 'v' . $sensor. '.0';
        $dbm_value = snmp_get($device, $object_type, $oap_flags, $object_ident);

        if (is_numeric($dbm_value)) {
            $sensor_oid = '.1.3.6.1.4.1.40989.10.16.' . $oap_edfa . '.1.' .$options['id'] . '.0';
            $sensor_description = 'C' . $oap_edfa . ' EDFA ' . $options['desc'];
            $index = $device['device_id'] . '::' . $object_ident . '::' .  $object_type;

            discover_sensor(
                $valid['sensor'], 
                'current', 
                $device, 
                $sensor_oid,
                $index,
                'fs-nmu', 
                $sensor_description,
                $oap_current_divisor,
                $oap_current_multiplier,
                -4,
                -3,
                3,
                4,
                $dbm_value,
                'snmp',
                null, null, null,
                $object_ident
                );
        } else {
            break;
        }
    }
}