<?php
$hardware = 'Fiberhome '.snmp_get($device, 'sysDescr.0', '-Oqv', 'GEPON-OLT-COMMON-MIB');
$version  = str_replace('"', '', snmp_get($device, 'sysHardVersion.0', '-Ovq', 'GEPON-OLT-COMMON-MIB')).' - '.str_replace('"', '', snmp_get($device, 'sysSoftVersion.0', '-Ovq', 'GEPON-OLT-COMMON-MIB'));
$features = 'Olt '.snmp_get($device, 'sysDescr.0', '-Oqv', 'GEPON-OLT-COMMON-MIB');
