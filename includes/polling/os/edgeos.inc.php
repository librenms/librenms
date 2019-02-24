<?php

// Version is second word in sysDescr
list(, $version) = explode(' ', $device['sysDescr']);
preg_match('/v[0-9]+.[0-9]+.[0-9]+/', $version, $matches);
$version = $matches[0];

// fix for hardware missing info
$hw_fix = snmp_get($device, '.1.3.6.1.2.1.25.4.2.1.5.3818', '-Ovq');
if (strpos('running on', $hw_fix) === false) {
    $hw_fix = snmp_get($device, '.1.3.6.1.2.1.25.4.2.1.5.3819', '-Ovq');
}
preg_match('/(?<=UBNT )(.*)(?= running on)/', $hw_fix, $matches);
$hardware = $matches[0];

$features = '';
