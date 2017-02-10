<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$cambium_type = $poll_device['sysDescr'];
$PMP = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
$version = $cambium_type;
$filtered_words = array(
        'timing',
        'timeing',
);
if (strstr($cambium_type, 'BHUL450')) {
    $masterSlaveMode = str_replace($filtered_words, "", snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
    $hardware = 'PTP 450 '.$masterSlaveMode;
    $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    include 'includes/polling/wireless/canopy-generic.inc.php';
} elseif (strstr($cambium_type, 'BHUL')) {
    $masterSlaveMode = str_replace($filtered_words, "", snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
    $hardware = 'PTP 230 '. $masterSlaveMode;
    $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    include 'includes/polling/wireless/canopy-generic.inc.php';
} elseif (strstr($cambium_type, 'BH20') || strstr($cambium_type, 'BH10')) {
    $masterSlaveMode = str_replace($filtered_words, "", snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
    $hardware = 'PTP 100 '. $masterSlaveMode;
    $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    include 'includes/polling/wireless/canopy-generic.inc.php';
} elseif (strstr($cambium_type, 'CMM')) {
    $hardware = 'CMM';
    include 'includes/polling/wireless/canopy-generic.inc.php';
} elseif (strstr($PMP, 'MIMO OFDM')) {
    if (strstr($version, "AP")) {
        $hardware = 'PMP 450 AP';
    } elseif (strstr($version, "SM")) {
        $hardware = 'PMP 450 SM';
    } else {
        $hardware = 'PMP 450';
    }
    include 'includes/polling/wireless/canopy-generic.inc.php';
} elseif (strstr($PMP, 'OFDM')) {
    if (strstr($version, "AP")) {
        $hardware = 'PMP 430 AP';
    } elseif (strstr($version, "SM")) {
        $hardware = 'PMP 430 SM';
    } else {
        $hardware = 'PMP 430';
    }
    include 'includes/polling/wireless/canopy-generic.inc.php';
} else {
    if (strstr($version, "AP")) {
        $hardware = 'PMP 100 AP';
    } elseif (strstr($version, "SM")) {
        $hardware = 'PMP 100 SM';
    } else {
        $hardware = 'PMP 100';
    }
    include 'includes/polling/wireless/canopy-generic.inc.php';
}
