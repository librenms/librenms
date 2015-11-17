<?php

	$version      = trim(snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.1.6.0", "-Ovq"),'"');
	$hardware     = trim(snmp_get($device, "ENTITY-MIB::entPhysicalDescr.1", "-Ovq"),'"');
	$serial       = trim(snmp_get($device, "1.3.6.1.2.1.47.1.1.1.1.11.1", "-Ovq"),'"');


//
//// SNMPv2-MIB::sysDescr.0  Brocade VDX Switch.
//if (preg_match('/Brocade ([\s\d\w]+)/', $poll_device['sysDescr'], $hardware)) {
//    $hardware = $hardware[1];
//}
//