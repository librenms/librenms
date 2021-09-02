<?php
/**
 * cxr-networks.inc.php
 *
 * LibreNMS CXR Serial Ports include
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
 * @copyright  PipoCanaja 2018
 * @author     PipoCanaja <PipoCanaja@gmail.com>
 */
$cxr_stats = snmpwalk_cache_oid($device, 'portTable', [], 'CXR-TS-MIB');
$cxr_stats = snmpwalk_cache_oid($device, 'portStatsTable', $cxr_stats, 'CXR-TS-MIB');

d_echo($cxr_stats);

//We'll create dummy ifIndexes to add the Serial Ports to the LibreNMS port view.
//These devices are showing only a few interfaces, 1000 seems a sufficient offset.

$offset = 1000;

foreach ($cxr_stats as $index => $serialport_stats) {
    $curIfIndex = $offset + $index;
    $port_stats[$curIfIndex]['ifDescr'] = "SerialPort$index";
    $port_stats[$curIfIndex]['ifType'] = 'rs232'; //rs232
    $port_stats[$curIfIndex]['ifName'] = "Serial$index";
    $port_stats[$curIfIndex]['ifInOctets'] = $serialport_stats['bytesReceiveFromV24'];
    $port_stats[$curIfIndex]['ifOutOctets'] = $serialport_stats['bytesSendToV24'];
    $port_stats[$curIfIndex]['ifSpeed'] = preg_replace('/[^0-9.]/', '', $serialport_stats['baudRate']);
    $port_stats[$curIfIndex]['ifAdminStatus'] = 'up';
    $port_stats[$curIfIndex]['ifOperStatus'] = 'up';
    $port_stats[$curIfIndex]['ifAlias'] = "Port $index, " . $serialport_stats['terminalType'] . ', ' . $serialport_stats['mode'] . ', ' . $serialport_stats['baudRate'] . ' ' . $serialport_stats['nbParStop'];
    if ($serialport_stats['aliasIpAddress'] != '0.0.0.0') {
        $port_stats[$curIfIndex]['ifAlias'] .= ', Alias IP: ' . $serialport_stats['aliasIpAddress'] . ':' . $serialport_stats['tcpPort'];
    }
    if ($serialport_stats['remoteIpAddress'] != '0.0.0.0') {
        $port_stats[$curIfIndex]['ifAlias'] .= ', Remote IP: ' . $serialport_stats['remoteIpAddress'] . ':' . $serialport_stats['remoteTcpPort'];
    }
}
