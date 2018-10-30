<?php
//SNMPv2-MIB::sysDescr.0 = STRING: EdgeSwitch 48-Port 750W, 1.1.2.4767216, Linux 3.6.5-f4a26ed5
//SNMPv2-MIB::sysDescr.0 = STRING: USW-24P-250, 3.3.15.3976, Linux 3.6.5
//SNMPv2-MIB::sysDescr.0 = STRING: EdgeSwitch 24-Port 250W, 1.6.0.4900860, Linux 3.6.5-f4a26ed5, 1.0.0.4857129
//SNMPv2-MIB::sysDescr.0 = STRING: EdgePoint Switch 16-Port, 1.6.0.4900860, Linux 3.6.5-f4a26ed5, 1.0.0.4857129
if (preg_match('/^(EdgeSwitch .*|EdgePoint Switch .*|USW-.*), (.*), Linux .*$/', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $version = $regexp_result[2];
    $serial = trim(snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.11.1", "-Ovq"), '" ');
}
