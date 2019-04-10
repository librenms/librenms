<?php

//all information below matches what would be reported by remote mgmt card
//hardware = UPS model
//serial   = UPS serial number as reported by RMCARD
//version  = Firmware revision of RMCARD

$cyberpower_oids = [
    'hardware' => '.1.3.6.1.4.1.3808.1.1.1.1.1.1.0',
    'version'  => '.1.3.6.1.4.1.3808.1.1.1.1.2.4.0',
    'serial'   => '.1.3.6.1.4.1.3808.1.1.1.1.2.9.0',
];

$returned_oids = snmp_get_multi_oid($device, $cyberpower_oids, '-OUQn', 'CPS-MIB');

$hardware = $returned_oids[$cyberpower_oids['hardware']];
$version  = $returned_oids[$cyberpower_oids['version']];
$serial   = $returned_oids[$cyberpower_oids['serial']];
