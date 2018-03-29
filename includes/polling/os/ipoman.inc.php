<?php

// IPOMANII-MIB::ipmIdentAgentSoftwareVersion.0 = STRING: "PDU System v1.06 (SN 11130141042005)"
$SoftwareVersion = trim(snmp_get($device, 'ipmIdentAgentSoftwareVersion.0', '-OQv', 'IPOMANII-MIB'), '" ');

preg_match('/v(.*) \(SN (.*)\)/', $SoftwareVersion, $matches);
if ($matches[1]) {
    $version = $matches[1];
}

if ($matches[2]) {
    $serial = $matches[2];
}
