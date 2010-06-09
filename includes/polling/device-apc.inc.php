<?php


$serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.6.0", "-OQv", "", ""),'"');

$hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.5.0", "-OQv", "", ""),'"');
$hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.2.0", "-OQv", "", ""),'"');

$version = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.3.0", "-OQv", "", ""),'"');

?>
