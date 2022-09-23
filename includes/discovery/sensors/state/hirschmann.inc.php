<?php

if ($device['os'] == 'hirschmann') {
    ///////////////////////////
    /// Power Supply Status ///
    ///////////////////////////
    $oid = snmpwalk_cache_multi_oid($device, 'hmPSTable', [], 'HMPRIV-MGMT-SNMP-MIB');
    $cur_oid = '.1.3.6.1.4.1.248.14.1.2.1.3.';

    if (is_array($oid)) {
        //Create State Index
        $state_name = 'hmPowerSupplyStatus';
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'notInstalled'],
            ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ];
        create_state_index($state_name, $states);

        foreach ($oid as $index => $entry) {
            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, 'hmPowerSupplyStatus' . $index, $state_name, 'Power Supply ' . $oid[$index]['hmPSID'], 1, 1, null, null, null, null, $oid[$index]['hmPowerSupplyStatus'], 'snmp', 'hmPowerSupplyStatus' . $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, 'hmPowerSupplyStatus' . $index);
        }
    }

    ////////////////////////////////
    /// Common LED Status States ///
    ////////////////////////////////
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'off'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'green'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'yellow'],
        ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'red'],
    ];

    ///////////////////////////////
    /// LED Status Power Supply ///
    ///////////////////////////////
    $temp = snmp_get($device, 'hmLEDRSPowerSupply.0', '-Ovqe', 'HMPRIV-MGMT-SNMP-MIB');
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.1.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRSPowerSupply';
        create_state_index($state_name, $states);

        $descr = 'LED Status Power Supply';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, 'hmLEDRSPowerSupply.' . $index, $state_name, $descr, 1, 1, null, null, null, null, $temp, 'snmp', 'hmLEDRSPowerSupply.' . $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, 'hmLEDRSPowerSupply.' . $index);
    }

    //////////////////////////
    /// LED Status Standby ///
    //////////////////////////
    $temp = snmp_get($device, 'hmLEDRStandby.0', '-Ovqe', 'HMPRIV-MGMT-SNMP-MIB');
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.2.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRStandby';
        create_state_index($state_name, $states);

        $descr = 'LED Status Standby';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, 'hmLEDRStandby.' . $index, $state_name, $descr, 1, 1, null, null, null, null, $temp, 'snmp', 'hmLEDRStandby.' . $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, 'hmLEDRStandby.' . $index);
    }

    /////////////////////////////////////
    /// LED Status Redundancy Manager ///
    /////////////////////////////////////
    $temp = snmp_get($device, 'hmLEDRSRedundancyManager.0', '-Ovqe', 'HMPRIV-MGMT-SNMP-MIB');
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.3.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRSRedundancyManager';
        create_state_index($state_name, $states);

        $descr = 'LED Status Redundancy Manager';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, 'hmLEDRSRedundancyManager.' . $index, $state_name, $descr, 1, 1, null, null, null, null, $temp, 'snmp', 'hmLEDRSRedundancyManager.' . $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, 'hmLEDRSRedundancyManager.' . $index);
    }

    ////////////////////////
    /// LED Status Fault ///
    ////////////////////////
    $temp = snmp_get($device, 'hmLEDRSFault.0', '-Ovqe', 'HMPRIV-MGMT-SNMP-MIB');
    $cur_oid = '.1.3.6.1.4.1.248.14.1.1.35.1.4.0';
    $index = '0';

    if (is_numeric($temp)) {
        //Create State Index
        $state_name = 'hmLEDRSFault';
        create_state_index($state_name, $states);

        $descr = 'LED Status Fault';
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid, 'hmLEDRSFault.' . $index, $state_name, $descr, 1, 1, null, null, null, null, $temp, 'snmp', 'hmLEDRSFault.' . $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, 'hmLEDRSFault.' . $index);
    }
}
