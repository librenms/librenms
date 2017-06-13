<?php

list($hardware, $features, $version) = explode(',', $poll_device['sysDescr']);
$serial = trim(snmp_get($device, "agentInventorySerialNumber.0", "-Ovq", "NETGEAR-SWITCHING-MIB"), '" ');
