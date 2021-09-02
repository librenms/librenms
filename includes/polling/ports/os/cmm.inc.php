<?php
/**
 * cmm.inc.php
 *
 * LibreNMS CMM Ports include
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
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */
$cmm_stats = snmpwalk_group($device, 'cmmSwitchTable', 'CMM3-MIB');
$cmm_stats = snmpwalk_group($device, 'cmmPortTable', 'CMM3-MIB', 1, $cmm_stats);

$required = [
    'ifInOctets' => 'rxOctets',
    'ifOutOctets' => 'txOctets',
    'ifInUcastPkts' => 'rxUnicastPkts',
    'ifOutUcastPkts' => 'txUnicastPkts',
    'ifInErrors' => 'rxDropPkts',
    'ifOutErrors' => 'txDropPkts',
    'ifInBroadcastPkts' => 'rxBroadcastPkts',
    'ifOutBroadcastPkts' => 'txBroadcastPkts',
    'ifInMulticastPkts' => 'rxMulticastPkts',
    'ifOutMulticastPkts' => 'txMulticastPkts',
];
$cmm_ports = [];
foreach ($cmm_stats as $index => $port) {
    $cmm_port = [];

    foreach ($required as $ifEntry => $IfxStat) {
        $cmm_port[$ifEntry] = $cmm_stats[$index][$IfxStat];
    }
    $cmm_port['ifName'] = 'CMM Port ' . $port['portNumber'];
    $cmm_port['ifDescr'] = 'CMM Port ' . $port['portNumber'];
    $cmm_port['ifDuplex'] = ($cmm_stats[$index]['duplexStatus'] == 1 ? 'fullDuplex' : 'halfDuplex');
    $cmm_port['ifSpeed'] = ($cmm_stats[$index]['linkSpeed'] == 1 ? '100000000' : '10000000');
    $cmm_port['ifOperStatus'] = ($cmm_stats[$index]['linkStatus'] == 1 ? 'up' : 'down');
    $cmm_port['ifType'] = 'ethernetCsmacd';
    array_push($cmm_ports, $cmm_port);
}

$port_stats = array_replace_recursive($cmm_ports, $port_stats);
