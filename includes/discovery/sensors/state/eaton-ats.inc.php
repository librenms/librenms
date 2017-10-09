<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */

$state = snmp_get($device, "ats2OperationMode.0", "-Ovqe", 'EATON-ATS2-MIB');
$cur_oid = '.1.3.6.1.4.1.534.10.2.2.4.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'ats2OperationMode';
    $state_index_id = create_state_index($state_name);
    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'initialization',0,1,1) ,
             array($state_index_id,'diagnosis',0,2,1) ,
             array($state_index_id,'off',0,3,2) ,
             array($state_index_id,'source1',0,4,0) ,
             array($state_index_id,'source2',0,5,0) ,
             array($state_index_id,'safe',0,6,1) ,
             array($state_index_id,'fault',0,7,2) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Operation Mode', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

$state = snmp_get($device, "ats2InputStatusDephasing.0", "-Ovqe", 'EATON-ATS2-MIB');
$cur_oid = '.1.3.6.1.4.1.534.10.2.3.1.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'ats2InputStatusDephasing';
    $state_index_id = create_state_index($state_name);
    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'normal',0,1,1) ,
             array($state_index_id,'outOfRange',0,2,1) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Dephasing Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
