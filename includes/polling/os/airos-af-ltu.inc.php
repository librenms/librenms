<?php

$osdata = snmp_get_multi_oid($device, ['afLTUDevModel.0', 'afLTUFirmwareVersion.0'], '-OQUs', 'UBNT-AFLTU-MIB');
$hardware = $osdata['afLTUDevModel.0'];
$version = $osdata['afLTUFirmwareVersion.0'];
