<?php
/*
 * LibreNMS FortiGate percentage sensors
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2024 LibreNMS
 * @author     Rudy Broersma
 */

$fgDhcpTables = snmpwalk_cache_multi_oid($device, 'fgDhcpTables', [], 'FORTINET-FORTIGATE-MIB');

if (! empty($fgDhcpTables)) {
    $vgEntNames = snmpwalk_cache_multi_oid($device, 'fgVdEntName', [], 'FORTINET-FORTIGATE-MIB');

    foreach ($fgDhcpTables as $index => $entry) {
        $indexSplit = explode('.', $index);
        $fgVdomID = $indexSplit[0];
        $fgDhcpServerID = $indexSplit[1];

        discover_sensor(
            $valid['sensor'],
            'percent',
            $device,
            '.1.3.6.1.4.1.12356.101.23.2.1.1.2.' . $index,
            'fgDhcpLeaseUsage.' . $index,
            'fortigate',
            $vgEntNames[$fgVdomID]['fgVdEntName'] . ' Server ID ' . $fgDhcpServerID . ' Pool Usage',
            1,
            1,
            null,
            null,
            90,
            95,
            $entry['fgDhcpLeaseUsage'],
            'snmp',
            null,
            null,
            null,
            'DHCP Usage',
            'gauge'
        );
    }
}
