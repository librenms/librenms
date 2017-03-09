<?php

// PDU
$serial = trim(snmp_get($device, 'PowerNet-MIB::rPDUIdentSerialNumber.0', '-OQv'), '"');

if ($serial == '') {
    // ATS
    $serial = trim(snmp_get($device, 'PowerNet-MIB::atsIdentSerialNumber.0', '-OQv'), '"');
}

if ($serial == '') {
    // UPS
    $serial = trim(snmp_get($device, 'PowerNet-MIB::upsAdvIdentSerialNumber.0', '-OQv'), '"');
}

if ($serial == '') {
    // Masterswitch/AP9606
    $serial = trim(snmp_get($device, 'PowerNet-MIB::sPDUIdentSerialNumber.0', '-OQv'), '"');
}

if ($serial == '') {
    // InRow chiller
    $serial = trim(snmp_get($device, 'PowerNet-MIB::airIRRCUnitIdentSerialNumber.0', '-OQv'), '"');
}

if ($serial == '') {
    // InfraStruxure
    $serial = trim(snmp_get($device, 'PowerNet-MIB::isxModularPduIdentSerialNumber.0', '-OQv'), '"');
}

//
// PDU
$hardware  = trim(snmp_get($device, 'PowerNet-MIB::rPDUIdentModelNumber.0', '-OQv'), '"');
$hardware .= ' '.trim(snmp_get($device, 'PowerNet-MIB::rPDUIdentHardwareRev.0', '-OQv'), '"');

if ($hardware == ' ') {
    // ATS
    $hardware  = trim(snmp_get($device, 'PowerNet-MIB::atsIdentModelNumber.0', '-OQv'), '"');
    $hardware .= ' '.trim(snmp_get($device, 'PowerNet-MIB::atsIdentHardwareRev.0', '-OQv'), '"');
}

if ($hardware == ' ') {
    // UPS
    $hardware  = trim(snmp_get($device, 'PowerNet-MIB::upsBasicIdentModel.0', '-OQv'), '"');
    $hardware .= ' '.trim(snmp_get($device, 'PowerNet-MIB::upsAdvIdentFirmwareRevision.0', '-OQv'), '"');
}

if ($hardware == ' ') {
    // Masterswitch/AP9606
    $hardware  = trim(snmp_get($device, 'PowerNet-MIB::sPDUIdentModelNumber.0', '-OQv'), '"');
    $hardware .= ' '.trim(snmp_get($device, 'PowerNet-MIB::sPDUIdentHardwareRev.0', '-OQv'), '"');
}

if ($hardware == ' ') {
    // InRow chiller
    $hardware  = trim(snmp_get($device, 'PowerNet-MIB::airIRRCUnitIdentModelNumber.0', '-OQv'), '"');
    $hardware .= ' '.trim(snmp_get($device, 'PowerNet-MIB::airIRRCUnitIdentHardwareRevision.0', '-OQv'), '"');
}

if ($hardware == ' ') {
    // InfraStruxure
    $hardware  = trim(snmp_get($device, 'PowerNet-MIB::isxModularPduIdentModelNumber.0', '-OQv'), '"');
    $hardware .= ' '.trim(snmp_get($device, 'PowerNet-MIB::isxModularPduIdentMonitorCardHardwareRev.0', '-OQv'), '"');
}

$AOSrev = trim(snmp_get($device, '1.3.6.1.4.1.318.1.4.2.4.1.4.1', '-OQv', '', ''), '"');
$APPrev = trim(snmp_get($device, '1.3.6.1.4.1.318.1.4.2.4.1.4.2', '-OQv', '', ''), '"');

if ($AOSrev == '') {
    // PDU
    $version = trim(snmp_get($device, 'PowerNet-MIB::rPDUIdentFirmwareRev.0', '-OQv'), '"');

    if ($version == '') {
        // ATS
        $version = trim(snmp_get($device, 'PowerNet-MIB::atsIdentFirmwareRev.0', '-OQv'), '"');
    }

    if ($version == '') {
        // Masterswitch/AP9606
        $version = trim(snmp_get($device, 'PowerNet-MIB::sPDUIdentFirmwareRev.0', '-OQv'), '"');
    }

    if ($version == '') {
        // InRow chiller
        $version = trim(snmp_get($device, 'PowerNet-MIB::airIRRCUnitIdentFirmwareRevision.0', '-OQv'), '"');
    }

    if ($version == '') {
        // InfraStruxure
        $version = trim(snmp_get($device, 'PowerNet-MIB::isxModularPduIdentMonitorCardFirmwareAppRev.0', '-OQv'), '"');
    }
} else {
    $version = "AOS $AOSrev / App $APPrev";
}//end if
