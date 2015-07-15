<?php

if (preg_match('/^Pacific Broadband Networks .+\n.+ Version ([^,]+), .+\n.+\n.+\nSerial num:([^,]+), .+/', $poll_device['sysDescr'], $regexp_result)) {
	$version = $regexp_result[1];
	$serial = $regexp_result[2];
	
# for PBN CPE 120/121 	
} elseif (strstr(snmp_get($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn"), ".1.3.6.1.4.1.11606.24.1.1.10")) {
	$version = snmp_get($device, "1.3.6.1.4.1.11606.24.1.1.6.0", "-Ovq");
	$hardware = snmp_get($device, "1.3.6.1.4.1.11606.24.1.1.7.0", "-Ovq");
	$features = snmp_get($device, "1.3.6.1.4.1.11606.24.1.1.10.0", "-Ovq");
	$serial = snmp_get($device, "1.3.6.1.4.1.11606.24.1.1.4.0", "-Ovq");
}

?>
