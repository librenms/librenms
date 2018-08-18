<?php

$get_hardware = explode(',', snmp_get($device, "1.3.6.1.2.1.47.1.1.1.1.2.888624", "-OQv", "SNMPv2-SMI"));
$hardware = "SmartAX " . $get_hardware[0];