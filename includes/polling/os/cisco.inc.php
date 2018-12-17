<?php

if (preg_match('/^Cisco IOS Software.*?, .+? Software(\, )?([\s\w\d]+)? \([^\-]+-([\w\d]+)-\w\), Version ([^,]+)/', $device['sysDescr'], $regexp_result)) {
    $features = $regexp_result[3];
    $version  = $regexp_result[4];
    $hardware = $regexp_result[2];
    $tmp      = preg_split("/\\r\\n|\\r|\\n/", $version);
    if (!empty($tmp[0])) {
        $version = $tmp[0];
    }
}

echo "\n".$device['sysDescr']."\n";

$oids = ['entPhysicalModelName.1', 'entPhysicalContainedIn.1', 'entPhysicalName.1', 'entPhysicalSoftwareRev.1', 'entPhysicalModelName.1001', 'entPhysicalContainedIn.1001', 'cardDescr.1', 'cardSlotNumber.1'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');

if ($data[1]['entPhysicalContainedIn'] == '0') {
    if (!empty($data[1]['entPhysicalSoftwareRev'])) {
        $version = $data[1]['entPhysicalSoftwareRev'];
    }

    if (!empty($data[1]['entPhysicalName']) && $data[1]['entPhysicalName'] != 'Switch System') {
        $hardware = $data[1]['entPhysicalName'];
    }

    if (!empty($data[1]['entPhysicalModelName'])) {
        $hardware = $data[1]['entPhysicalModelName'];
    }
}

// if ($slot_1 == "-1" && strpos($descr_1, "No") === FALSE) { $ciscomodel = $descr_1; }
// if (($contained_1 == "0" || $name_1 == "Chassis") && strpos($model_1, "No") === FALSE) { $ciscomodel = $model_1; list($version_1) = explode(",",$ver_1); }
// if ($contained_1001 == "0" && strpos($model_1001, "No") === FALSE) { $ciscomodel = $model_1001; }
// $ciscomodel = str_replace("\"","",$ciscomodel);
// if ($ciscomodel) { $hardware = $ciscomodel; unset($ciscomodel); }
if (empty($hardware)) {
    $hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB');
}

$serial = get_main_serial($device);
