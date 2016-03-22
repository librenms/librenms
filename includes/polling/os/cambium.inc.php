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

$cambium_type = snmp_get($device, 'sysDescr.0', '-Oqv', '');
$is_epmp = snmp_get($device, 'sysObjectID.0', '-Oqv', '');
$PMP = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
$version = $cambium_type;
$filtered_words = array(
        'timing',
        'timeing',
);
if (strstr($cambium_type, 'Cambium PTP 50650')) {
    $masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CAMBIUM-PTP650-MIB"));
    $hardware = 'PTP 650 '. $masterSlaveMode;
    include 'includes/polling/wireless/cambium-650.inc.php';
}
else if (strstr($cambium_type, 'BHUL450')) {
    $masterSlaveMode = str_replace($filtered_words,"",snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
    $hardware = 'PTP 450 '.$masterSlaveMode;
    $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    include 'includes/polling/wireless/cambium-generic.inc.php';
}
else if (strstr($cambium_type, 'PTP250')) {
    $masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CAMBIUM-PTP250-MIB"));
    $hardware = 'PTP 250 '.$masterSlaveMode;
    include 'includes/polling/wireless/cambium-250.inc.php';
}
else if (strstr($cambium_type, 'BHUL')) {
    $masterSlaveMode = str_replace($filtered_words,"",snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
    $hardware = 'PTP 230 '. $masterSlaveMode;
    $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    include 'includes/polling/wireless/cambium-generic.inc.php';
}
else if (strstr($cambium_type, 'BH20') || strstr($cambium_type, 'BH10')) {
    $masterSlaveMode = str_replace($filtered_words,"",snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
    $hardware = 'PTP 100 '. $masterSlaveMode;
    $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    include 'includes/polling/wireless/cambium-generic.inc.php';
}
else if (strstr($is_epmp, '.17713.21')) {
    $epmp_ap = snmp_get($device, 'wirelessInterfaceMode.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    $epmp_number = snmp_get($device, 'cambiumSubModeType.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    if ($epmp_ap == 1) {
        if ($epmp_number == 5) {
            $hardware = 'ePTP Master';
        }
        else {
            $hardware = 'ePMP AP';
        }
    }
    else if ($epmp_ap == 2) {
        if ($epmp_number == 4) {
            $hardware = 'ePTP Slave';
        }
        else {
            $hardware = 'ePMP SM';
        }
    }
    $version = snmp_get($device, 'cambiumCurrentuImageVersion.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    include 'includes/polling/wireless/cambium-epmp.inc.php';
}
else if (strstr($cambium_type, 'CMM')) {
    $hardware = 'CMM';
    include 'includes/polling/wireless/cambium-generic.inc.php';
}
else if (strstr($PMP, 'MIMO OFDM')) {
    if (strstr($version, "AP")) {
        $hardware = 'PMP 450 AP';
    }
    else if (strstr($version, "SM")) {
        $hardware = 'PMP 450 SM';
    }
    else {
        $hardware = 'PMP 450';
    }
    include 'includes/polling/wireless/cambium-generic.inc.php';
}
else if (strstr($PMP, 'OFDM')) {
    if (strstr($version, "AP")) {
        $hardware = 'PMP 430 AP';
    }
    else if (strstr($version, "SM")) {
        $hardware = 'PMP 430 SM';
    }
    else {
        $hardware = 'PMP 430';
    }
    include 'includes/polling/wireless/cambium-generic.inc.php';
}
else {
    if (strstr($version, "AP")) {
        $hardware = 'PMP 100 AP';
    }
    else if (strstr($version, "SM")) {
        $hardware = 'PMP 100 SM';
    }
    else {
        $hardware = 'PMP 100';
    }
    include 'includes/polling/wireless/cambium-generic.inc.php';
}