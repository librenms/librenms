<?php

$oids = snmpwalk_group($device, 'bcnHaServiceStatus', 'BCN-HA-MIB');

if (!empty($oids)) {
    //Create State Index
    $state_status = 'bcnHaSerOperState';
    $states = array(
         array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'standalone'),
         array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'active'),
         array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'passive'),
         array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'stopped'),
         array('value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'stopping'),
         array('value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'becomingActive'),
         array('value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'becomingPassive'),
         array('value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'fault'),


     );

    create_state_index($state_status, $states);

    $num_oid_status = '.1.3.6.1.4.1.13315.3.1.5.2.1.1.0';
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $num_oid_status, $index, $state_status, $state_status, '0', '0', null, null, null, null, $entry, 'snmp', $index);

        //Create Sensor To State Index For Status
        create_sensor_to_state_index($device, $state_status, $index);
    }
}

$oids_dhcp = snmpwalk_group($device, 'bcnDhcpv4SerOperState', 'BCN-DHCPV4-MIB');

if (!empty($oids_dhcp)) {
    //Create State Index
    $state_dhcp = 'bcnDhcpv4SerOperState';
    $states_dhcp = array(
         array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'running'),
         array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'notRunning'),
         array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'starting'),
         array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'stopping'),
         array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'stopped'),
         array('value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'fault'),

     );

    create_state_index($state_dhcp, $states_dhcp);

    $num_oid_dhcpstatus = '.1.3.6.1.4.1.13315.3.1.1.2.1.1.0';
    foreach ($oids_dhcp as $index => $entry_dhcp) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $num_oid_dhcpstatus, $index, $state_dhcp, $state_dhcp, '0', '0', null, null, null, null, $entry_dhcp, 'snmp', $index);

        //Create Sensor To State Index For Status
        create_sensor_to_state_index($device, $state_dhcp, $index);
    }
}

$oids_dns = snmpwalk_group($device, 'bcnDnsSerOperState', 'BCN-DNS-MIB');

if (!empty($oids_dns)) {
    //Create State Index
    $state_dns = 'bcnDnsSerOperState';
    $states_dns = array(
         array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'running'),
         array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'notRunning'),
         array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'starting'),
         array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'stopping'),
         array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'fault'),

     );

    create_state_index($state_dns, $states_dns);

    $num_oid_dnsstatus = '.1.3.6.1.4.1.13315.3.1.2.2.1.1.0';
    foreach ($oids_dns as $index => $entry_dns) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $num_oid_dnsstatus, $index, $state_dns, $state_dns, '0', '0', null, null, null, null, $entry_dns, 'snmp', $index);

        //Create Sensor To State Index For Status
        create_sensor_to_state_index($device, $state_dns, $index);
    }
}

$oids_ntp = snmpwalk_group($device, 'bcnNtpSerOperState', 'BCN-NTP-MIB');

if (!empty($oids_ntp)) {
    //Create State Index
    $state_ntp = 'bcnNtpSerOperState';
    $states_ntp = array(
         array('value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'running'),
         array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'notRunning'),
         array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'starting'),
         array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'stopping'),
         array('value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'fault'),

     );

    create_state_index($state_ntp, $states_ntp);

    $num_oid_ntpstatus = '.1.3.6.1.4.1.13315.3.1.4.2.1.1.0';
    foreach ($oids_ntp as $index => $entry_ntp) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $num_oid_ntpstatus, $index, $state_ntp, $state_ntp, '0', '0', null, null, null, null, $entry_ntp, 'snmp', $index);

        //Create Sensor To State Index For Status
        create_sensor_to_state_index($device, $state_ntp, $index);
    }
}

