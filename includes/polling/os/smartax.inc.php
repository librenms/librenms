<?php

$hardware = "SmartAX " . preg_replace('/[\r\n\"]+/', ' ', snmp_get($device, "1.3.6.1.2.1.47.1.1.1.1.2.875160", "-OQv", "SNMPv2-SMI"));
