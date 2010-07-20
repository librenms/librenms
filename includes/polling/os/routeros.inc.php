<?php

$version = trim(snmp_get($device, "1.3.6.1.4.1.14988.1.1.4.4.0", "-OQv", "", ""),'"');
$version .= " Level " . trim(snmp_get($device, "1.3.6.1.4.1.14988.1.1.4.3.0", "-OQv", "", ""),'"');

?>
