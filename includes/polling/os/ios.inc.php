<?php
/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage polling
 * @copyright  (C) 2016 Librenms
 */

if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $device['sysDescr'], $regexp_result)) {
    $features = $regexp_result[1];
    $version  = $regexp_result[2];
} elseif (preg_match('/Cisco Internetwork Operating System Software\s+IOS \(tm\) [^ ]+ Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $device['sysDescr'], $regexp_result)) {
    $features = $regexp_result[1];
    $version  = $regexp_result[2];
}

$oids = ['entPhysicalModelName.1', 'entPhysicalContainedIn.1', 'entPhysicalName.1', 'entPhysicalSoftwareRev.1', 'entPhysicalModelName.1001', 'entPhysicalContainedIn.1001', 'cardDescr.1', 'cardSlotNumber.1'];

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

if (!empty($data[1000]['entPhysicalModelName'])) {
    $hardware = $data[1000]['entPhysicalModelName'];
} elseif (!empty($data[1000]['entPhysicalContainedIn'])) {
    $hardware = $data[$data[1000]['entPhysicalContainedIn']]['entPhysicalName'];
} elseif (!empty($data[1001]['entPhysicalModelName'])) {
    $hardware = $data[1001]['entPhysicalModelName'];
} elseif (!empty($data[1001]['entPhysicalContainedIn'])) {
    $hardware = $data[$data[1001]['entPhysicalContainedIn']]['entPhysicalName'];
}

if (empty($hardware)) {
    $hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB');
}

$serial = get_main_serial($device);

if (strstr($hardware, 'cisco819')) {
    include 'includes/polling/wireless/cisco-wwan.inc.php';
}
