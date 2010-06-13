<?php

$version = trim(snmp_get($device, "1.3.6.1.4.1.705.1.1.4.0", "-OQv", "", ""),'"');
$hardware = $sysDescr;
  
?>
