<?php

/**
 * timos.inc.php
 *
 * LibreNMS include timos (nokia) virtual router ports
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Vitali Kari
 * @author     Vitali Kari <vitali.kari@gmail.com>
 */

// get all virtual router ports and statistics
$timos_vrf_stats = SnmpQuery::enumStrings()->abortOnFailure()->walk([
    'TIMETRA-VRTR-MIB::vRtrIfName',
    'TIMETRA-VRTR-MIB::vRtrIfAdminState',
    'TIMETRA-VRTR-MIB::vRtrIfOperState',
    'TIMETRA-VRTR-MIB::vRtrIfDescription',
    'TIMETRA-VRTR-MIB::vRtrIfSpeed',
    'TIMETRA-VRTR-MIB::vRtrIfType',
    'TIMETRA-VRTR-MIB::vRtrIfMtu',
    'TIMETRA-VRTR-MIB::vRtrIfRxBytes',
    'TIMETRA-VRTR-MIB::vRtrIfTxBytes',
    'TIMETRA-VRTR-MIB::vRtrIfRxPkts',
    'TIMETRA-VRTR-MIB::vRtrIfTxPkts',
    'TIMETRA-VRTR-MIB::vRtrIfAlias',
])->table(2);

// Merge all virtual routing ports into one
$timos_stats = [];
foreach ($timos_vrf_stats as $vrf) {
    foreach ($vrf as $index => $stats) {
        $timos_stats[$index] = $stats;
    }
}
unset($timos_vrf_stats);

$translate = [
    'ifName' => 'TIMETRA-VRTR-MIB::vRtrIfName',
    'ifAlias' => 'TIMETRA-VRTR-MIB::vRtrIfAlias',
    'ifAdminStatus' => 'TIMETRA-VRTR-MIB::vRtrIfAdminState',
    'ifOperStatus' => 'TIMETRA-VRTR-MIB::vRtrIfOperState',
    'ifDescr' => 'TIMETRA-VRTR-MIB::vRtrIfDescription',
    'ifSpeed' => 'TIMETRA-VRTR-MIB::vRtrIfSpeed',
    'ifType' => 'TIMETRA-VRTR-MIB::vRtrIfType',
    'ifMtu' => 'TIMETRA-VRTR-MIB::vRtrIfMtu',
    'ifHCInOctets' => 'TIMETRA-VRTR-MIB::vRtrIfRxBytes',
    'ifHCOutOctets' => 'TIMETRA-VRTR-MIB::vRtrIfTxBytes',
    'ifHCInUcastPkts' => 'TIMETRA-VRTR-MIB::vRtrIfRxPkts',
    'ifHCOutUcastPkts' => 'TIMETRA-VRTR-MIB::vRtrIfTxPkts',
];

foreach ($timos_stats as $index => $value) {
    foreach ($translate as $ifEntry => $ifVrtrEntry) {
        $port_stats[$index][$ifEntry] = $value[$ifVrtrEntry] ?? $port_stats[$index][$ifEntry] ?? null;
    }
    if (empty($timos_ports[$index]['ifDescr'])) {
        $port_stats[$index]['ifDescr'] = $value['ifName'] ?? null;
    }
}

unset($timos_stats, $translate);
