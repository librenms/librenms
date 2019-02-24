<?php
/**
 * compas.inc.php
 *
 * LibreNMS state sensor discovery module for Alpha Comp@s UPS
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
 * @copyright  2019 Paul Parsons
 * @author     Paul Parsons <paul@cppmonkey.net>
 */
$batteryTestState = snmp_get($device, 'es1dc1DataBatBatTestState.0', '-Ovqe', 'SITE-MONITORING-MIB');
$curOID = '.1.3.6.1.4.1.26854.3.2.1.20.1.20.1.13.3.72.0';
$index = 'es1dc1DataBatBatTestState';
if (is_numeric($batteryTestState)) {
    //Create State Index
    $state_name = 'es1dc1DataBatBatTestState';
    $state_index_id = create_state_index($state_name);
    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'Never Tested',0,0,0) ,
            array($state_index_id,'Success',0,1,0),
            array($state_index_id,'On Going',0,2,1),
            array($state_index_id,'Failed: Timeout',0,3,1),
            array($state_index_id,'Failed: Vbus Too Low',0,4,1),
            array($state_index_id,'Failed: Load Too Low',0,5,1),
            array($state_index_id,'Failed: AC Failure',0,6,2),
            array($state_index_id,'Failed: Canceled',0,7,1),
            array($state_index_id,'Failed: LVD Opened',0,8,1),
            array($state_index_id,'Failed: No Battery',0,9,1)
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
    $descr = 'Battery Test Status';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $curOID, $index, $state_name, $descr, '1', '1', null, null, null, null, $batteryTestState);
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
$dcMode = snmp_get($device, 'es1dc1DataSystemDCMode.0', '-Ovqe', 'SITE-MONITORING-MIB');
$curOID = '.1.3.6.1.4.1.26854.3.2.1.20.1.20.1.13.3.1.0';
$index = 'es1dc1DataSystemDCMode';
if (is_numeric($dcMode)) {
    //Create State Index
    $state_name = 'es1dc1DataSystemDCMode';
    $state_index_id = create_state_index($state_name);
    //Create State Translation
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id,'Float',0,0,0) ,
            array($state_index_id,'Equalize',0,1,0),
            array($state_index_id,'Battery Test',0,2,1),
            array($state_index_id,'AC Failure',0,3,2),
            array($state_index_id,'Safe',0,5,0)
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
    $descr = 'System DC Mode';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $curOID, $index, $state_name, $descr, '1', '1', null, null, null, null, $dcMode);
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
