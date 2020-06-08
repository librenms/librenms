<?php
$temp_data = snmp_get_multi_oid($device, '.1.3.6.1.2.1.47.1.1.1.1.7.1 fsaSysVersion.0 .1.3.6.1.2.1.47.1.1.1.1.11.1', '-OUQs', 'FORTINET-FORTISANDBOX-MIB');
$hardware = $temp_data['mib-2.47.1.1.1.1.7.1'];
$version = $temp_data['fsaSysVersion.0'];
$serial = $temp_data['mib-2.47.1.1.1.1.11.1'];
unset($temp_data);
