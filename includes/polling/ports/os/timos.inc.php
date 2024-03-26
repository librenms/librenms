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
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfName', [], 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfAlias', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfDescription', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfSpeed', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfType', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfMtu', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfRxBytes', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfTxBytes', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfRxPkts', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');
$timos_vrf_stats = snmpwalk_cache_twopart_oid($device, 'vRtrIfTxPkts', $timos_vrf_stats, 'TIMETRA-VRTR-MIB');

// Merge all virtual routing ports into one
$timos_stats = [];
foreach ($timos_vrf_stats as $vrf) {
    foreach ($vrf as $index => $stats) {
        $timos_stats[$index] = $stats;
    }
}
unset($timos_vrf_stats);

$translate = [
    'ifName' => 'vRtrIfName',
    'ifAlias' => 'vRtrIfAlias',
    'ifDescr' => 'vRtrIfDescription',
    'ifSpeed' => 'vRtrIfSpeed',
    'ifType' => 'vRtrIfType',
    'ifMtu' => 'vRtrIfMtu',
    'ifHCInOctets' => 'vRtrIfRxBytes',
    'ifHCOutOctets' => 'vRtrIfTxBytes',
    'ifHCInUcastPkts' => 'vRtrIfRxPkts',
    'ifHCOutUcastPkts' => 'vRtrIfTxPkts',
];

$timos_ports = [];
foreach ($timos_stats as $index => $value) {
    foreach ($translate as $ifEntry => $ifVrtrEntry) {
        $timos_ports[$index][$ifEntry] = $timos_stats[$index][$ifVrtrEntry];
    }
    if (empty($timos_ports[$index]['ifDescr'])) {
        $timos_ports[$index]['ifDescr'] = $timos_ports[$index]['ifName'];
    }
}

$port_stats = array_replace_recursive($timos_ports, $port_stats);
unset($timos_ports);

# adding MPLS interface stats
$mplsStatCache = snmpwalk_cache_multi_oid($device, 'vRtrMplsifStatTable', [], 'TIMETRA-MPLS-MIB', 'nokia');
# the following for-loop changes the array keys to use the [$ifEntry] instead of [$index.$ifEntry]
foreach ($mplsStatCache as $key => $value) {
    [$index, $ifEntry] = explode('.', $key);
    $mplsStatCache[$ifEntry] = $mplsStatCache[$key];
    unset($mplsStatCache[$key]);
}
# the below simply adds the vRtrMplsifStats to the [$port_stats] array where applicable
$port_stats = array_replace_recursive($mplsStatCache, $port_stats);

# the following for-loop adds the checks if vRtrMplsifStats exist, if so it is being added to the ifHC counters
# this way the exisiting code in the main port poller definition can be kept as it is
foreach ($port_stats as $key => $value) {
    if (isset($port_stats[$key]['vRtrMplsIfTxPktCount'])) {
       $port_stats[$key]['ifHCOutUcastPkts'] = $port_stats[$key]['ifHCOutUcastPkts'] + $port_stats[$key]['vRtrMplsIfTxPktCount'];
       $port_stats[$key]['ifHCInUcastPkts'] = $port_stats[$key]['ifHCInUcastPkts'] + $port_stats[$key]['vRtrMplsIfRxPktCount'];
       $port_stats[$key]['ifHCOutOctets'] = $port_stats[$key]['ifHCOutOctets'] + $port_stats[$key]['vRtrMplsIfTxOctetCount'];
       $port_stats[$key]['ifHCInOctets'] = $port_stats[$key]['ifHCInOctets'] + $port_stats[$key]['vRtrMplsIfRxOctetCount'];
    }
}