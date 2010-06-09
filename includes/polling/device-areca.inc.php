<?php

$hardware = trim(snmp_get($device, "1.3.6.1.4.1.18928.1.1.1.1.0", "-OQv", "", ""),'"');
$version = trim(snmp_get($device, "1.3.6.1.4.1.18928.1.1.1.4.0", "-OQv", "", ""),'"');
$serial = trim(snmp_get($device, "1.3.6.1.4.1.18928.1.1.1.3.0", "-OQv", "", ""),'"');
  
?>
