<?php

$hardware     = trim(snmp_get($poll_device['sysDescr'], ".1.3.6.1.2.1.1.1.0", '-Ovq'), '"');
$version      = trim(snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.9.1", '-Ovq'), '"');
