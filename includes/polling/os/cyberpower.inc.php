<?php

if (begins_with('.1.3.6.1.4.1.3808.1.1.1', $device['sysObjectID'])) {
    $cyberpower_oids = [
        'hardware' => '.1.3.6.1.4.1.3808.1.1.1.1.1.1.0',
        'version'  => '.1.3.6.1.4.1.3808.1.1.1.1.2.1.0',
        'serial'   => '.1.3.6.1.4.1.3808.1.1.1.1.2.3.0',
    ];
} else {
    $cyberpower_oids = [
        'version'  => '.1.3.6.1.4.1.3808.1.1.3.1.3.0',
        'hardware' => '.1.3.6.1.4.1.3808.1.1.3.1.5.0',
        'serial'   => '.1.3.6.1.4.1.3808.1.1.3.1.6.0',
    ];
}
$returned_oids = snmp_get_multi_oid($device, $cyberpower_oids, '-OUQn', 'CPS-MIB');

$hardware = $returned_oids[$cyberpower_oids['hardware']];
$version  = $returned_oids[$cyberpower_oids['version']];
