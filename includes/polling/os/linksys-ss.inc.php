<?php
// Linksys OS details

$data = snmp_get_multi($device, ['rlPhdUnitGenParamModelName.1', 'rlPhdUnitGenParamHardwareVersion.1', 'rlPhdUnitGenParamSerialNum.1', 'rlPhdUnitGenParamSoftwareVersion.1', 'rlPhdUnitGenParamFirmwareVersion.1'], '-OQUs', 'LINKSYS-Physicaldescription-MIB');
$hardware = $data['1']['rlPhdUnitGenParamModelName'];
if ($data['1']['rlPhdUnitGenParamHardwareVersion']) {
    $hardware .= " " . $data['1']['rlPhdUnitGenParamHardwareVersion'];
}
$serial = $data['1']['rlPhdUnitGenParamSerialNum'];
$version = "SW: " . $data['1']['rlPhdUnitGenParamSoftwareVersion'];
$version .= ", FW: " . $data['1']['rlPhdUnitGenParamFirmwareVersion'];
