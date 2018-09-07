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
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @link       http://librenms.org
* @copyright  PipoCanaja 2018
* @author     PipoCanaja <PipoCanaja@gmail.com>
*/

$cxr_stats = snmpwalk_cache_oid($device, 'portTable', array(), 'CXR-TS-MIB');
$cxr_stats = snmpwalk_cache_oid($device, 'portStatsTable', $cxr_stats, 'CXR-TS-MIB');

//unset($f5_stats[0]);

//foreach ($ifmib_oids as $oid) {
//    echo "$oid ";
//    $tmp_port_stats = snmpwalk_cache_oid($device, $oid, $tmp_port_stats, 'IF-MIB', null, '-OQUst');
//}

$required = array(
    'ifName' => 'sysIfxStatName',
    'ifHighSpeed' => 'sysIfxStatHighSpeed',
    'ifHCInOctets' => 'sysIfxStatHcInOctets',
    'ifHCOutOctets' => 'sysIfxStatHcOutOctets',
    'ifHCInUcastPkts' => 'sysIfxStatHcInUcastPkts',
    'ifHCOutUcastPkts' => 'sysIfxStatHcOutUcastPkts',
    'ifHCInMulticastPkts' => 'sysIfxStatHcInMulticastPkts',
    'ifHCOutMulticastPkts' => 'sysIfxStatHcOutMulticastPkts',
    'ifHCInBroadcastPkts' => 'sysIfxStatHcInBroadcastPkts',
    'ifHCOutBroadcastPkts' => 'sysIfxStatHcOutBroadcastPkts',
    'ifConnectorPresent' => 'sysIfxStatConnectorPresent',
    'ifAlias' => 'sysIfxStatAlias',
);

d_echo($cxr_stats);

foreach ($cxr_stats as $index => $serialport_stats) {
    $port_stats[1000+$index]['ifDescr']="SerialPort$index";
    $port_stats[1000+$index]['ifType']='rs232'; //rs232
    $port_stats[1000+$index]['ifName']="Serial$index";
    $port_stats[1000+$index]['ifAlias']="Port $index, " . $serialport_stats['terminalType'] . ", " . $serialport_stats['mode'] . ", " . $serialport_stats['baudRate']." ".$serialport_stats['nbParStop'];
    if ($serialport_stats['aliasIpAddress'] != "0.0.0.0") {
        $port_stats[1000+$index]['ifAlias'] .= ", Alias IP: " . $serialport_stats['aliasIpAddress'] . ":".$serialport_stats['tcpPort'];
    }
    if ($serialport_stats['remoteIpAddress'] != "0.0.0.0") {
        $port_stats[1000+$index]['ifAlias'] .= ", Remote IP: " . $serialport_stats['remoteIpAddress'] . ":".$serialport_stats['remoteTcpPort'];
    }

    $port_stats[1000+$index]['ifInOctets']=$serialport_stats['bytesReceiveFromV24'];
    $port_stats[1000+$index]['ifOutOctets']=$serialport_stats['bytesSendToV24'];
    $port_stats[1000+$index]['ifSpeed']=$serialport_stats['baudRate'];
    $port_stats[1000+$index]['ifSpeed']=preg_replace("/[^0-9.]/", '', $port_stats[1000+$index]['ifSpeed']);
    $port_stats[1000+$index]['ifAdminStatus']='up';
    $port_stats[1000+$index]['ifOperStatus']='up';
}


