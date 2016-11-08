<?php
//
// Discovery of Power supplies on Juniper Wireless (Trapeze) devices.
//
if ($device['os'] == 'trapeze') {
    $temp = snmpwalk_cache_multi_oid($device, 'trpzSysPowerSupplyEntry', array(), 'TRAPEZE-NETWORKS-SYSTEM-MIB', 'trapeze');
    $cur_oid = '.1.3.6.1.4.1.14525.4.8.1.1.13.1.2.1.2.'; //trpzSysPowerSupplyStatus
    if (!empty($temp)) {
        //Create State Index
        $state_name = 'trpzSysPowerSupplyStatus';
        $state_index_id = create_state_index($state_name);
		/*
		Create State Translation
		
		TrpzSysPowerSupplyStatus
		INTEGER {
                    other        (1),
                    unknown      (2),
                    ac-failed    (3),
                    dc-failed    (4),
                    ac-ok-dc-ok  (5)
                }

		The LibreNMS generic states is derived from Nagios:
		0 = OK
		1 = Warning
		2 = Critical
		3 = Unknown
		*/
        if ($state_index_id !== null) {
            $states = array(
                 array($state_index_id,'other',0,1,3) ,
                 array($state_index_id,'unknown',0,2,3) ,
                 array($state_index_id,'ac-failed',1,3,1) ,
                 array($state_index_id,'dc-failed',1,4,1) ,
                 array($state_index_id,'ac-ok-dc-ok',1,5,0) ,
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
        foreach ($temp as $index => $entry) {
                //Discover Sensors
                $descr = $temp[$index]['trpzSysPowerSupplyDescr'];
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index]['trpzSysPowerSupplyStatus'], 'snmp', $index);
                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
        }//end foreach
    }//end if empty
}//end if not trapeze