<?php

$osdata = snmp_get_multi_oid($device, ['ubntModel.0', 'ubntVersion.0'], '-OQUs', 'UBNT-EdgeMAX-MIB');
$hardware = 'Ubiquiti ' . $osdata['ubntModel.0'];
$version = $osdata['ubntVersion.0'];
