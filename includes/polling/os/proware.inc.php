<?php

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.14752.1.3.1.1.0', '-OQv', '', ''), '"');
$version  = trim(snmp_get($device, '1.3.6.1.4.1.14752.1.3.1.4.0', '-OQv', '', ''), '"');
$serial   = trim(snmp_get($device, '1.3.6.1.4.1.14752.1.3.1.3.0', '-OQv', '', ''), '"');

if (isHexString($serial)) {
    // Sometimes firmware outputs serial as hex-string
    $serial = snmp_hexstring($serial);
}
