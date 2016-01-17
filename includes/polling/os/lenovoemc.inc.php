<?php
$hardware = snmp_get($device, 'deviceDescr.0', '-Ovq', 'IOMEGANAS-MIB');
$version = 'N/A';
$serial = 'N/A';
?>
