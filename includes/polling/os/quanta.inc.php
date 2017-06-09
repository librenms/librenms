<?php

list($hardware, $features, $version) = explode(',', $poll_device['sysDescr']);
$serial = trim(snmp_get($device, ".1.3.6.1.4.1.4413.1.1.1.1.1.4.0", "-Ovq"), '" ');
