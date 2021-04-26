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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */
$packetlogic_stats = snmpwalk_group($device, 'netDeviceTable', 'PACKETLOGIC-CHANNEL-MIB', 1, []);
$packetlogic_stats = snmpwalk_group($device, 'channelInfoTable', 'PACKETLOGIC-CHANNEL-MIB', 1, $packetlogic_stats);

$channelTypes = [
    [
        'type' => 'channelExternal',
        'name' => 'External',
    ],
    [
        'type' => 'channelInternal',
        'name' => 'Internal',
    ],
];

$required = [
    'ifInOctets' => 'RxBytes',
    'ifOutOctets' => 'TxBytes',
    'ifInUcastPkts' => 'RxPackets',
    'ifOutUcastPkts' => 'TxPackets',
    'ifInErrors' => 'RxErrors',
    'ifOutErrors' => 'TxErrors',
];

// Media Types as per PACKETLOGIC-CHANNEL-MIB
$mediaType = [
    0 => ['ifDuplex' => null, 'ifSpeed' => 0, 'label'=> 'linkdown'],
    1 => ['ifDuplex' => 'halfDuplex', 'ifSpeed' => '10000000', 'label' => 'hd10'],
    2 => ['ifDuplex' => 'fullDuplex', 'ifSpeed' => '10000000', 'label' => 'fd10'],
    3 => ['ifDuplex' => 'halfDuplex', 'ifSpeed' => '100000000', 'label' => 'hd100'],
    4 => ['ifDuplex' => 'fullDuplex', 'ifSpeed' => '100000000', 'label' => 'fd100'],
    5 => ['ifDuplex' => 'fullDuplex', 'ifSpeed' => '1000000000', 'label' => 'fd1000'],
    6 => ['ifDuplex' => 'fullDuplex', 'ifSpeed' => '10000000000', 'label' => 'fd10000'],
];

foreach ($packetlogic_stats as $index => $port) {
    $procera_port = [];
    foreach ($channelTypes as $cType) {
        foreach ($required as $ifEntry => $IfxStat) {
            $procera_port[$ifEntry] = $packetlogic_stats[$index][$cType['type'] . $IfxStat][0];
        }
        $negotiatedMedia = $packetlogic_stats[$index][$cType['type'] . 'NegotiatedMedia'][0];
        $procera_port['ifName'] = $packetlogic_stats[$index]['channelName'][0] . ' ' . $cType['name'];
        $procera_port['ifDescr'] = $packetlogic_stats[$index]['channelName'][0] . ' ' . $cType['name'];
        $procera_port['ifConnectorPresent'] = ($negotiatedMedia != '0' ? 'true' : 'false');
        $procera_port['ifOperStatus'] = ($packetlogic_stats[$index]['channelActive'][0] == 1 ? 'up' : 'down');
        $procera_port['ifSpeed'] = $mediaType[$negotiatedMedia]['ifSpeed'];
        $procera_port['ifDuplex'] = $mediaType[$negotiatedMedia]['ifDuplex'];
        $procera_port['ifType'] = 'ethernetCsmacd';
        $port_stats[$index] = $procera_port;
    }
}

unset($channelTypes, $packetlogic_stats, $procera_port, $mediaType, $negotiatedMedia);
