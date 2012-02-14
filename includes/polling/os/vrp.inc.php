<?php

$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.2011.2.33.20.1.1.1.3.0", "-OQv"),'"');

preg_match("/Version .*\n/",$poll_device['sysDescr'], $matches);
$version = trim(str_replace("Version ","",$matches[0]));

?>