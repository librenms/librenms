<?php

$version                 = preg_replace('/.+ version (.+) running on .+ (\S+)$/', '\\1||\\2', $device['sysDescr']);
list($version,$hardware) = explode('||', $version);
$serial                  = snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.11.1", "-OQv");
