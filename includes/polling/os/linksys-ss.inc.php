<?php

$hardware = snmp_get($device, 'LINKSYS-Physicaldescription-MIB::rlPhdUnitGenParamModelName.1', '-OQv');
$hardware .= " " . snmp_get($device, 'LINKSYS-Physicaldescription-MIB::rlPhdUnitGenParamHardwareVersion.1', '-OQv');
$serial = snmp_get($device, 'LINKSYS-Physicaldescription-MIB::rlPhdUnitGenParamSerialNum.1', '-OQv');

$version = "SW: " . snmp_get($device, 'LINKSYS-Physicaldescription-MIB::rlPhdUnitGenParamSoftwareVersion.1', '-OQv');
$version .= ", FW: " . snmp_get($device, 'LINKSYS-Physicaldescription-MIB::rlPhdUnitGenParamFirmwareVersion.1', '-OQv');

