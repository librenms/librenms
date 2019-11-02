<?php

$version = snmp_get($device, 'infoMgmtFirmwareVersion.0', '-OQv', 'NEXANS-BM-MIB');
$hardware = snmp_get($device, 'infoDescr.0', '-OQv', 'NEXANS-BM-MIB');
