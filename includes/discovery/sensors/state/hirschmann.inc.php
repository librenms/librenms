<?php

if ($device['os'] == 'hirschmann') {
    ///////////////////////////
    /// Power Supply Status ///
    ///////////////////////////
    $oid = snmpwalk_cache_multi_oid($device, 'hmPSTable', array(), 'HMPRIV-MGMT-SNMP-MIB');
    $cur_oid = '.1.3.6.1.4.1.248.14.1.2.1.3.';

    if (is_array($oid)) {
        //Create State Index
        $state_name = 'hmPowerSupplyStatus';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'ok',0,1,0) ,
                array($state_index_id,'failed',0,2,2) ,
                array($state_index_id,'notInstalled',0,3,1) ,
                array($state_index_id,'unknown',0,4,3)
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

        foreach ($oid as $index => $entry) {
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'Power Supply '.$oid[$index]['hmPSID'], '1', '1', null, null, null, null, $oid[$index]['hmPowerSupplyStatus'], 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }

   ///////////////////////////////
   /// LED Status Power Supply ///
   ///////////////////////////////
    $temp = snmp_get($device, "hmLEDRSPowerSupply.0", "-Ovqe", "HMPRIV-MGMT-SNMP-MIB");
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.1.0';
    $index = '0';

    if (is_numeric($temp)) {
       //Create State Index
        $state_name = 'hmLEDRSPowerSupply';
        $state_index_id = create_state_index($state_name);

       //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'off',0,1,0) ,
                array($state_index_id,'green',0,2,0) ,
                array($state_index_id,'yellow',0,3,1) ,
                array($state_index_id,'red',0,4,2)
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

        $descr = 'LED Status Power Supply';
    //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

    //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }

   //////////////////////////
   /// LED Status Standby ///
   //////////////////////////
    $temp = snmp_get($device, "hmLEDRStandby.0", "-Ovqe", "HMPRIV-MGMT-SNMP-MIB");
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.2.0';
    $index = '0';
 
    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRStandby';
        $state_index_id = create_state_index($state_name);

       //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'off',0,1,0) ,
                array($state_index_id,'green',0,2,0) ,
                array($state_index_id,'yellow',0,3,1) ,
                array($state_index_id,'red',0,4,2)
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
 
        $descr = 'LED Status Standby';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }

    /////////////////////////////////////
    /// LED Status Redundancy Manager ///
    /////////////////////////////////////
    $temp = snmp_get($device, "hmLEDRSRedundancyManager.0", "-Ovqe", "HMPRIV-MGMT-SNMP-MIB");
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.3.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRSRedundancyManager';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'off',0,1,0) ,
                array($state_index_id,'green',0,2,0) ,
                array($state_index_id,'yellow',0,3,1) ,
                array($state_index_id,'red',0,4,2)
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

        $descr = 'LED Status Redundancy Manager';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }

    ////////////////////////
    /// LED Status Fault ///
    ////////////////////////
    $temp = snmp_get($device, "hmLEDRSFault.0", "-Ovqe", "HMPRIV-MGMT-SNMP-MIB");
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.4.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRSFault';
        $state_index_id = create_state_index($state_name);

        //Create State Translation
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id,'off',0,1,0) ,
                array($state_index_id,'green',0,2,0) ,
                array($state_index_id,'yellow',0,3,1) ,
                array($state_index_id,'red',0,4,2)
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

        $descr = 'LED Status Fault';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
