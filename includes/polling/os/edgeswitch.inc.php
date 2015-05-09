<?php
//SNMPv2-MIB::sysDescr.0 = STRING: EdgeSwitch 48-Port 750W, 1.1.2.4767216, Linux 3.6.5-f4a26ed5
if( preg_match('/^(EdgeSwitch .*), (.*), Linux .*$/', $poll_device['sysDescr'], $regexp_result)) {
	$hardware = $regexp_result[1];
	$version = $regexp_result[2];
	$serial = trim(snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.11.1", "-Ovq") , '" ');
}
