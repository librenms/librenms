<?php
/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2016 Librenms
 */
$oids = ['entPhysicalModelName.1', 'entPhysicalContainedIn.1', 'entPhysicalName.1', 'entPhysicalSoftwareRev.1', 'entPhysicalModelName.1001', 'entPhysicalContainedIn.1001', 'cardDescr.1', 'cardSlotNumber.1'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');

if ($data[1]['entPhysicalContainedIn'] == '0') {
    if (! empty($data[1]['entPhysicalSoftwareRev'])) {
        $version = $data[1]['entPhysicalSoftwareRev'];
    }

    if (! empty($data[1]['entPhysicalName'])) {
        $hardware = $data[1]['entPhysicalName'];
    }

    if (! empty($data[1]['entPhysicalModelName'])) {
        $hardware = $data[1]['entPhysicalModelName'];
    }
}

if (! empty($data[1000]['entPhysicalModelName'])) {
    $hardware = $data[1000]['entPhysicalModelName'];
} elseif (! empty($data[1000]['entPhysicalContainedIn'])) {
    $hardware = $data[$data[1000]['entPhysicalContainedIn']]['entPhysicalName'];
} elseif (! empty($data[1001]['entPhysicalModelName'])) {
    $hardware = $data[1001]['entPhysicalModelName'];
} elseif (! empty($data[1001]['entPhysicalContainedIn'])) {
    $hardware = $data[$data[1001]['entPhysicalContainedIn']]['entPhysicalName'];
}
