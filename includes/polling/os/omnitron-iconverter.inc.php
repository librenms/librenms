<?php

// SNMPv2-MIB::sysDescr.1 = STRING: "Omnitron iConverter GM4-HPOE 8991T14F v4.5.28 s/n 00711452 - [x]"
$description = $device['sysDescr'];

preg_match('~Omnitron\siConverter\s(?\'hardware\'.*?)\s(?\'version\'v.*) s\/n~', $description, $matches);

if ($matches['hardware']) {
    $hardware = $matches['hardware'];
}

if ($matches['version']) {
    $version = $matches['version'];
}

$serial = snmp_get($device, 'serialnum.1.1', '-Ovq', 'OMNITRON-MIB');
