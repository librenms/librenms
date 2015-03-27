<?php
$version = "Big IP v";
$version .= trim(snmp_get($device, ".1.3.6.1.4.1.3375.2.1.4.2.0", "-OQv", "", ""),'"');
?>
