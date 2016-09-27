<?php

if ($device['os'] == 'f5') {
    echo 'F5 power supply: ';
    // Power Status OID (Value : 0 Bad, 1 Good, 2 NotPresent)
    $temp = snmpwalk_cache_multi_oid($device, 'sysChassisPowerSupplyTable' , array(), 'F5-BIGIP-SYSTEM-MIB');

    if (is_array($temp)) {

        //Create State Index
        $state_name = 'sysChassisPowerSupplyStatus';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id) {
            $states = array(
                 array($state_index_id,'Bad',0,0,2) ,
                 array($state_index_id,'Good',0,1,0) ,
                 array($state_index_id,'NotPresent',0,2,3) 
             );
            foreach($states as $value){ 
                $insert = array(
                    'state_index_id' => $value[0],
                    'state_descr' => $value[1],
                    'state_draw_graph' => $value[2],
                    'state_value' => $value[3],         // Value polled from device
                    'state_generic_value' => $value[4]  // from the Nagios standard 0=OK, 1=Warning, 2=Critical, 3=Unknown
                );
                dbInsert($insert, 'state_translations');
            }
        }

      foreach (array_keys($temp) as $index) {
          $descr           = "sysChassisPowerSupplyStatus.".$temp[$index]['sysChassisPowerSupplyIndex'];
          $current         = $temp[$index]['sysChassisPowerSupplyStatus'];
          $sensorType      = 'f5';
          $oid             = '1.3.6.1.4.1.3375.2.1.3.2.2.2.1.2.'.$index;
          $oid_status	 = snmp_get($device, $oid, '-Oqv');
          discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp',$index);

          //Create Sensor To State Index
          create_sensor_to_state_index($device, $state_name, $index);

      }

    }

}
