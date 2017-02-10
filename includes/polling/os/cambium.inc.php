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
$is_epmp = $poll_device['sysObjectID'];
$version = $cambium_type;
if (strstr($cambium_type, 'Cambium PTP 50650')) {
    $masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CAMBIUM-PTP650-MIB"));
    $hardware = 'PTP 650 '. $masterSlaveMode;
    include 'includes/polling/wireless/cambium-650.inc.php';
} elseif (strstr($cambium_type, 'PTP250')) {
    $masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CAMBIUM-PTP250-MIB"));
    $hardware = 'PTP 250 '.$masterSlaveMode;
    include 'includes/polling/wireless/cambium-250.inc.php';
} elseif (strstr($is_epmp, '.17713.21')) {
    $epmp_ap = snmp_get($device, 'wirelessInterfaceMode.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    $epmp_number = snmp_get($device, 'cambiumSubModeType.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    if ($epmp_ap == 1) {
        if ($epmp_number == 5) {
            $hardware = 'ePTP Master';
        } else {
            $hardware = 'ePMP AP';
        }
    } elseif ($epmp_ap == 2) {
        if ($epmp_number == 4) {
            $hardware = 'ePTP Slave';
        } else {
            $hardware = 'ePMP SM';
        }
    }
    $version = snmp_get($device, 'cambiumCurrentuImageVersion.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    include 'includes/polling/wireless/cambium-epmp.inc.php';
}
