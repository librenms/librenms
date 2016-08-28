<?php

// 7200 and IOS-XE (ASR1k)
if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $poll_device['sysDescr'], $regexp_result)) {
    $features = $regexp_result[1];
    $version  = $regexp_result[2];
} elseif (preg_match('/Cisco Internetwork Operating System Software\s+IOS \(tm\) [^ ]+ Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $poll_device['sysDescr'], $regexp_result)) {
    $features = $regexp_result[1];
    $version  = $regexp_result[2];
} // If we have not managed to match any IOS string yet (and that would be surprising)
// we can try to poll the Entity Mib to see what's inside
else {
    $oids = 'entPhysicalModelName.1 entPhysicalContainedIn.1 entPhysicalName.1 entPhysicalSoftwareRev.1 entPhysicalModelName.1001 entPhysicalContainedIn.1001 cardDescr.1 cardSlotNumber.1';

    $data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');

    if ($data[1]['entPhysicalContainedIn'] == '0') {
        if (!empty($data[1]['entPhysicalSoftwareRev'])) {
            $version = $data[1]['entPhysicalSoftwareRev'];
        }

        if (!empty($data[1]['entPhysicalName'])) {
            $hardware = $data[1]['entPhysicalName'];
        }

        if (!empty($data[1]['entPhysicalModelName'])) {
            $hardware = $data[1]['entPhysicalModelName'];
        }
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

// if(isset($cisco_hardware_oids[$poll_device['sysObjectID']])) { $hardware = $cisco_hardware_oids[$poll_device['sysObjectID']]; }
$serial = get_main_serial($device);


if (strstr($hardware, 'cisco819')) {
      include 'includes/polling/wireless/cisco-wwan.inc.php';
}
