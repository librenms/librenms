<?php
$luminato_tmp = snmp_get_multi_oid($device, ['deviceName.0', 'hwSerialNumber.0', 'swVersion.0'], '-OUQs', 'TELESTE-LUMINATO-MIB');
$hardware = $luminato_tmp['deviceName.0'];
$serial   = $luminato_tmp['hwSerialNumber.0'];
$version  = $luminato_tmp['swVersion.0'];
