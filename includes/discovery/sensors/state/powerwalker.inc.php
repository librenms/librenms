<?php
/**
 * powerwalker.inc.php
 *
 * LibreNMS state sensor discovery module for PowerWalker
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

if ($device['os'] == 'powerwalker') {
    $state = snmp_get($device, "upsBatteryStatus.0", "-Ovqe", "UPS-MIB");
    $cur_oid = '.1.3.6.1.2.1.33.1.2.1.0';
    $index = '0';

    if (is_numeric($state)) {
        //Create State Index
        $state_name = 'upsBatteryStatus';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'unknown',0,1,3) ,
                array($state_index_id,'batteryNormal',0,2,0) ,
                array($state_index_id,'batteryLow',0,3,1) ,
                array($state_index_id,'batteryDepleted',0,4,2) ,
            );
            foreach ($states as $value) {
                $insert = array(
                    'state_index_id' => $value[0],
                    'state_descr' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_value' => $value[3],
                    'state_generic_value' => $value[4]
                );
                dbInsert($insert, 'state_translations');
            }
        }

        $descr = 'Battery Status';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
