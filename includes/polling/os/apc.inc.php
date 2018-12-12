<?php
$apc_serial = snmp_get_multi_oid($device, ['rPDUIdentSerialNumber.0', 'atsIdentSerialNumber.0', 'upsAdvIdentSerialNumber.0', 'sPDUIdentSerialNumber.0', 'airIRRCUnitIdentSerialNumber.0', 'isxModularPduIdentSerialNumber.0', 'airIRRP100UnitIdentSerialNumber.0', 'airIRRP500UnitIdentSerialNumber.0'], '-OUQs', 'PowerNet-MIB');
$serial = trim(array_pop($apc_serial), '"');
$apc_model = snmp_get_multi_oid($device, ['rPDUIdentModelNumber.0', 'rPDUIdentModelNumber.0', 'upsBasicIdentModel.0', 'sPDUIdentModelNumber.0', 'airIRRCUnitIdentModelNumber.0', 'isxModularPduIdentModelNumber.0', 'airIRRP100UnitIdentModelNumber.0', 'airIRRP500UnitIdentModelNumber.0'], '-OUQs', 'PowerNet-MIB');
$hardware = trim(array_pop($apc_model), '"');
$apc_hardware = snmp_get_multi_oid($device, ['rPDUIdentHardwareRev.0', 'atsIdentHardwareRev.0', 'upsAdvIdentFirmwareRevision.0', 'sPDUIdentHardwareRev.0', 'airIRRCUnitIdentHardwareRevision.0', 'isxModularPduIdentMonitorCardHardwareRev.0', 'airIRRP100UnitIdentHardwareRevision.0', 'airIRRP500UnitIdentHardwareRevision.0'], '-OUQs', 'PowerNet-MIB');
$hardware .= ' ' . trim(array_pop($apc_hardware), '"');
$AOSrev = trim(snmp_get($device, '1.3.6.1.4.1.318.1.4.2.4.1.4.1', '-OQv', '', ''), '"');
$APPrev = trim(snmp_get($device, '1.3.6.1.4.1.318.1.4.2.4.1.4.2', '-OQv', '', ''), '"');
if ($AOSrev == '') {
    $apc_version = snmp_get_multi_oid($device, ['rPDUIdentFirmwareRev.0', 'atsIdentFirmwareRev.0', 'sPDUIdentFirmwareRev.0', 'airIRRCUnitIdentFirmwareRevision.0', 'isxModularPduIdentMonitorCardFirmwareAppRev.0', 'airIRRP100UnitIdentFirmwareRevision.0', 'airIRRP500UnitIdentFirmwareRevision.0'], '-OUQs', 'PowerNet-MIB');
    $version = trim(array_pop($apc_version), '"');
} else {
    $version = "AOS $AOSrev / App $APPrev";
}//end if
