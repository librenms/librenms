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
    'ifName'            => 'vRtrIfName',
    'ifAlias'           => 'vRtrIfAlias',
    'ifDescr'           => 'vRtrIfDescription',
    'ifSpeed'           => 'vRtrIfSpeed',
    'ifType'            => 'vRtrIfType',
    'ifMtu'             => 'vRtrIfMtu',
    'ifHCInOctets'      => 'vRtrIfRxBytes',
    'ifHCOutOctets'     => 'vRtrIfTxBytes',
    'ifHCInUcastPkts'   => 'vRtrIfRxPkts',
    'ifHCOutUcastPkts'  => 'vRtrIfTxPkts',
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
