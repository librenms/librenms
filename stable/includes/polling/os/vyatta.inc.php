<?php

list($features, $version) = explode("-", trim(str_replace("Vyatta", "", snmp_get($device, "SNMPv2-MIB::sysDescr.0", "-Oqv", "SNMPv2-MIB"))), 2);

?>
