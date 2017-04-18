<?php

if (preg_match('/^Pacific Broadband Networks .+\n.+ Version ([^,]+), .+\n.+\n.+\nSerial num:([^,]+), .+/', snmp_get($device, 'SNMPv2-MIB::sysDescr.0', '-Ovq'), $regexp_result)) {
    $version = $regexp_result[1];
    $serial  = $regexp_result[2];
}
