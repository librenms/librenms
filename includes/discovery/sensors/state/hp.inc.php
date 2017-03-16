<?php
/**
 * hp.inc.php
 *
 * LibreNMS state sensor discovery module for HP Hardware devices
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

$tables = array(
    // One could add more entrys from deviceGroup, but this will do as a start
    array('cpqDaPhyDrvStatus','.1.3.6.1.4.1.232.3.2.5.1.1.6.','cpqDaPhyDrvStatus','DriveStatus','CPQSINFO-MIB'),
    array('cpqDaPhyDrvSmartStatus','.1.3.6.1.4.1.232.3.2.5.1.1.57.','cpqDaPhyDrvSmartStatus','SmartStatus','CPQSINFO-MIB'),
);

$x=0;
foreach ($tables as $tablevalue) {
    $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), $tablevalue[4], 'hp');
    $cur_oid = $tablevalue[1];

    if (is_array($temp)) {
        //Create State Index
        $state_name = $tablevalue[2];
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            if ($state_name == 'cpqDaPhyDrvStatus') {
                $states = array(
                    array($state_index_id,'other',1,1,3),
                    array($state_index_id,'ok',1,2,0),
                    array($state_index_id,'failed',1,3,2),
                    array($state_index_id,'predictiveFailure',1,4,2),
                    array($state_index_id,'erasing',1,5,1),
                    array($state_index_id,'eraseDone',1,6,1),
                    array($state_index_id,'eraseQueued',1,7,1),
                    array($state_index_id,'ssdWearOut',1,8,2),
                    array($state_index_id,'notAuthenticated',1,9,3),
                );
            } elseif ($state_name == 'cpqDaPhyDrvSmartStatus') {
                $states = array(
                    array($state_index_id,'other',1,1,3),
                    array($state_index_id,'ok',1,2,0),
                    array($state_index_id,'replaceDrive',1,3,1),
                    array($state_index_id,'replaceDriveSSDWearOut',1,4,1),
                );
            }

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

        foreach ($temp as $index => $entry) {
            $descr = 'Disk #'.trim(snmp_get($device, ".1.3.6.1.4.1.232.3.2.5.1.1.5.1.$index", "-Ovqn"), '"');
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $x . $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);
            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
    $x++;
}
