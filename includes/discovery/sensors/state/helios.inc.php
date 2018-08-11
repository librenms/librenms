<?php

$oids = snmpwalk_group($device, 'mlRadioStatusFailoverStatus.2', 'IGNITENET-MIB');

if (!empty($oids)) {
    //Create State Index
    $state_name = 'RadioStatusFailoverState';
    $states = array(
         array('value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Failover Inactive'),
         array('value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Failover Active'),
     );
    create_state_index($state_name, $states);

    $num_oid = '.1.3.6.1.4.1.47307.1.4.3.1.2.2';
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $num_oid.$index, $index, $state_name, '10Ghz Radio Failover Status', '1', '1', null, null, null, null, $entry['RadioStatusFailoverState'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
