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
if (strstr($cambium_type, 'Cambium PTP 50650')) {
        $mib = ':CAMBIUM-PTP650-MIB';
} elseif (strstr($cambium_type, 'PTP250')) {
    $mib = ':CAMBIUM-PTP250-MIB';
} elseif (strstr($is_epmp, '.17713.21')) {
    $epmp_ap = snmp_get($device, 'wirelessInterfaceMode.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    $epmp_number = snmp_get($device, 'cambiumSubModeType.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
    if ($epmp_ap == 1) {
        if ($epmp_number != 1) {
            $mib = ':CAMBIUM-PMP80211-MIB';
        }
    } else {
        $mib = ':CAMBIUM-PMP80211-MIB';
    }
} else {
    $mib = ':WHISP-BOX-MIBV2-MIB';
}
