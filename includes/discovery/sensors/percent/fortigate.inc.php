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
 *
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma
 */

$fgDhcpTables = SnmpQuery::hideMIB()->walk('FORTINET-FORTIGATE-MIB::fgDhcpTables')->table(0);

if (! empty($fgDhcpTables['fgDhcpLeaseUsage'])) {
    foreach ($fgDhcpTables['fgDhcpLeaseUsage'] as $vdomID => $table) {
        $vdomName = SnmpQuery::enumStrings()->hideMIB()->get('FORTINET-FORTIGATE-MIB:fgVdEntName.' . $vdomID)->value();

        foreach ($table as $index => $value) {
            $indexSplit = explode('.', $index);
            $fgDhcpServerID = $indexSplit[1];

            discover_sensor(
                null,
                'percent',
                $device,
                '.1.3.6.1.4.1.12356.101.23.2.1.1.2.' . $vdomID . '.' . $fgDhcpServerID,
                'fgDhcpLeaseUsage.' . $vdomID . '.' . $fgDhcpServerID,
                'fortigate',
                $vdomName . ' Server ID ' . $fgDhcpServerID . ' Pool Usage',
                1,
                1,
                null,
                null,
                90,
                95,
                $value,
                'snmp',
                null,
                null,
                null,
                'DHCP Usage',
                'gauge'
            );
        }
    }
}
