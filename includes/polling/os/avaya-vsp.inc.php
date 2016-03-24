<?php

echo 'Doing Nortel/Avaya VSP ';

$sysObjectID = $poll_device['sysObjectID'];

// Try multiple ways of getting firmware version
$version = snmp_get($device, 'SNMPv2-SMI::enterprises.2272.1.1.7.0', '-Oqvn');
$version = explode(' on', $version);
$version = $version[0];

if ($version == '') {
    $version = snmp_get($device, 'SNMPv2-SMI::enterprises.45.1.6.4.2.1.10.0', '-
Oqvn');
    if ($version == '') {
        $version = 'Unknown Version';
    }
}

//get Serial Number
$serial = snmp_get($device, 'SNMPv2-SMI::enterprises.2272.1.4.2.0', '-Oqvn');
$serial = explode(' on', $serial);
$serial = $serial[0];

// Get hardware details
$sysDescr = $poll_device['sysDescr'];

$details = explode('  ', $sysDescr);
$details = str_replace('VSP-', 'Virtual Services Platform ', $details);

$hardware = explode(' (', $details[0]);
$hardware = $hardware[0];

// Is this a 5500 series or 5600 series stack?
$features = '';

$stack      = snmp_walk($device, 'SNMPv2-SMI::enterprises.45.1.6.3.3.1.1.6.8', '
-OsqnU');
$stack      = explode("\n", $stack);
$stack_size = count($stack);
if ($stack_size > 1) {
    $features = "Stack of $stack_size units";
}

$version  = str_replace('"', '', $version);
$features = str_replace('"', '', $features);
$hardware = str_replace('"', '', $hardware);
$serial = str_replace('"', '', $serial);
