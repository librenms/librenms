<?php

$serial = trim(snmp_get($device, "entPhysicalSerialNum.1", "-Osqv", "ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB"));

?>
