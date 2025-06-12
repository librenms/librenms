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
 *
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */
$cmm_stats = SnmpQuery::hideMib()->abortOnFailure()->walk([
    'CMM3-MIB::cmmSwitchTable',
    'CMM3-MIB::cmmPortTable',
])->table(1);

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
foreach ($cmm_stats as $cmm_stat) {
    $cmm_port = array_map(function ($IfxStat) use ($cmm_stat) {
        return $cmm_stat[$IfxStat];
    }, $required);

    $cmm_port['ifName'] = 'CMM Port ' . $cmm_stat['portNumber'];
    $cmm_port['ifDescr'] = 'CMM Port ' . $cmm_stat['portNumber'];
    $cmm_port['ifType'] = 'ethernetCsmacd';

    if (isset($cmm_stat['duplexStatus'])) {
        $cmm_port['ifDuplex'] = ($cmm_stat['duplexStatus'] == 1 ? 'fullDuplex' : 'halfDuplex');
    }
    if (isset($cmm_stat['linkSpeed'])) {
        $cmm_port['ifSpeed'] = ($cmm_stat['linkSpeed'] == 1 ? '100000000' : '10000000');
    }
    if (isset($cmm_stat['linkStatus'])) {
        $cmm_port['ifOperStatus'] = ($cmm_stat['linkStatus'] == 1 ? 'up' : 'down');
    }

    $cmm_ports[] = $cmm_port;
}

$port_stats = array_replace_recursive($cmm_ports, $port_stats);

unset($cmm_stats, $cmm_ports, $cmm_stat, $cmm_port, $required);
