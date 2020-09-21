<?php

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.18928.1.1.1.1.0', '-OQv', '', ''), '"');
if (! $hardware) {
    $hardware = trim(snmp_get($device, '1.3.6.1.4.1.18928.1.2.1.1.0', '-OQv', '', ''), '"');
}

$version = trim(snmp_get($device, '1.3.6.1.4.1.18928.1.1.1.4.0', '-OQv', '', ''), '"');
if (! $version) {
    $version = trim(snmp_get($device, '1.3.6.1.4.1.18928.1.2.1.4.0', '-OQv', '', ''), '"');
}

$serial = trim(snmp_get($device, '1.3.6.1.4.1.18928.1.1.1.3.0', '-OQv', '', ''), '"');
if (! $serial) {
    $serial = trim(snmp_get($device, '1.3.6.1.4.1.18928.1.2.1.3.0', '-OQv', '', ''), '"');
}

if (isHexString($serial)) {
    // Sometimes firmware outputs serial as hex-string
    $serial = snmp_hexstring($serial);
}
