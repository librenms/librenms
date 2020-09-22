<?php

$device['sysDescr'] = str_replace('IOS (tm)', 'IOS (tm),', $device['sysDescr']);
$device['sysDescr'] = str_replace(')  RELEASE', '), RELEASE', $device['sysDescr']);

echo "\n" . $device['sysDescr'] . "\n";

[,$features,$version] = explode(',', $device['sysDescr']);

$version = str_replace(' Version ', '', $version);
[,$features] = explode('(', $features);
[,$features] = explode('-', $features);

$oids = ['entPhysicalModelName.1', 'entPhysicalContainedIn.1', 'entPhysicalName.1', 'entPhysicalSoftwareRev.1', 'entPhysicalModelName.1001', 'entPhysicalContainedIn.1001', 'cardDescr.1', 'cardSlotNumber.1'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');

if ($data[1]['entPhysicalContainedIn'] == '0') {
    if (isset($data[1]['entPhysicalSoftwareRev']) && $data[1]['entPhysicalSoftwareRev'] != '') {
        $version = $data[1]['entPhysicalSoftwareRev'];
    }

    if (isset($data[1]['entPhysicalName']) && $data[1]['entPhysicalName'] != '') {
        $hardware = $data[1]['entPhysicalName'];
    }

    if (isset($data[1]['entPhysicalModelName']) && $data[1]['entPhysicalModelName'] != '') {
        $hardware = $data[1]['entPhysicalModelName'];
    }
}

[$version] = explode(',', $version);

// if ($slot_1 == "-1" && strpos($descr_1, "No") === FALSE) { $ciscomodel = $descr_1; }
// if (($contained_1 == "0" || $name_1 == "Chassis") && strpos($model_1, "No") === FALSE) { $ciscomodel = $model_1; list($version_1) = explode(",",$ver_1); }
// if ($contained_1001 == "0" && strpos($model_1001, "No") === FALSE) { $ciscomodel = $model_1001; }
// $ciscomodel = str_replace("\"","",$ciscomodel);
// if ($ciscomodel) { $hardware = $ciscomodel; unset($ciscomodel); }
if ($hardware == '') {
    $hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB:ALTEON-ROOT-MIB');
}

if (strpos($device['sysDescr'], 'IOS XR')) {
    [,$version] = explode(',', $device['sysDescr']);
    $version = trim($version);
    [,$version] = explode(' ', $version);
    [$version] = explode("\n", $version);
    trim($version);
}
