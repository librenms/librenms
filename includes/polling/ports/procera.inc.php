<?php
/**
* procera.inc.php
*
* LibreNMS Procera Ports include
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
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @link       http://librenms.org
* @copyright  2017 Paul Heinrichs
* @author     Paul Heinrichs <pdheinrichs@gmail.com>
*/

$packetlogic_stats = snmpwalk_cache_oid($device, 'netDeviceTable', array(), 'PACKETLOGIC-CHANNEL-MIB');
$packetlogic_info = snmpwalk_cache_oid($device, 'channelInfoTable', array(), 'PACKETLOGIC-CHANNEL-MIB');

$channelTypes = array(
    array(
        'type' => 'channelExternal',
        'name' => 'External'
    ),
    array(
        'type' => 'channelInternal',
        'name' => 'Internal'
    )
);

$required = array(
    'ifInOctets' => 'RxBytes',
    'ifOutOctets' => 'TxBytes',
    'ifInUcastPkts' => 'RxPackets',
    'ifOutUcastPkts' => 'TxPackets',
    'ifInErrors' => 'RxErrors',
    'ifOutErrors' => 'TxErrors',
);

foreach ($packetlogic_stats as $index => $port) {
    $procera_port = [];
    foreach ($channelTypes as $cType) {
        foreach ($required as $ifEntry => $IfxStat) {
            $procera_port[$ifEntry] = $packetlogic_stats[$index][$cType['type'].$IfxStat];
        }
        $procera_port['ifName'] = $packetlogic_info[$index]['channelName']. ' '.$cType['name'];
        $procera_port['ifDescr'] = $packetlogic_info[$index]['channelName']. ' '.$cType['name'];
        $procera_port['ifConnectorPresent'] = ($packetlogic_info[$index]['NegotiatedMedia'] != 'linkdown' ? "true" : "false");
        $procera_port['ifOperStatus'] = ($packetlogic_info[$index]['channelActive'] === 'active' ? "up" : "down");
        $procera_port['ifType'] = 'ethernetCsmacd';
        array_push($port_stats, $procera_port);
    }
}

unset($packetlogic_info, $channelTypes, $packetlogic_stats, $procera_port);
