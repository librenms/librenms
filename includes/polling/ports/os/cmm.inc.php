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

foreach ($cmm_stats as $cmm_index => $cmm_port) {
    foreach ($required as $ifEntry => $IfxStat) {
        $port_stats[$cmm_index][$ifEntry] = $cmm_port[$IfxStat];
    }

    $port_stats[$cmm_index]['ifName'] = 'CMM Port ' . $cmm_port['portNumber'];
    $port_stats[$cmm_index]['ifDescr'] = 'CMM Port ' . $cmm_port['portNumber'];
    $port_stats[$cmm_index]['ifType'] = 'ethernetCsmacd';

    if (isset($cmm_port['duplexStatus'])) {
        $port_stats[$cmm_index]['ifDuplex'] = ($cmm_port['duplexStatus'] == 1 ? 'fullDuplex' : 'halfDuplex');
    }
    if (isset($cmm_port['linkSpeed'])) {
        $port_stats[$cmm_index]['ifSpeed'] = ($cmm_port['linkSpeed'] == 1 ? '100000000' : '10000000');
    }
    if (isset($cmm_port['linkStatus'])) {
        $port_stats[$cmm_index]['ifOperStatus'] = ($cmm_port['linkStatus'] == 1 ? 'up' : 'down');
    }
}

unset($cmm_stats, $cmm_port, $required);
