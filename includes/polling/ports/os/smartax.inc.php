<?php
/**
 * smartax.inc.php
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
 * @copyright  2020 Roman Tutkevich
 * @author     Roman Tutkevich <race.fdm@gmail.com>
 */
$inoctets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.15', []);    //  hwGponOltEthernetStatisticReceivedBytes
$outoctets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.30', []);   //  hwGponOltEthernetStatisticSendBytes
$inbpackets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.5', []);   //  hwGponOltEthernetStatisticReceivedBroadcastPakts
$outbpackets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.20', []); //  hwGponOltEthernetStatisticSendBroadcastPakts
$inmpackets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.6', []);   //  hwGponOltEthernetStatisticReceivedMulticastPakts
$outmpackets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.21', []); //  hwGponOltEthernetStatisticSendMulticastPakts
$inupackets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.7', []);   //  hwGponOltEthernetStatisticReceivedUnicastPakts
$outupackets = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.2011.6.128.1.1.4.21.1.22', []); //  hwGponOltEthernetStatisticSendUnicastPakts

foreach ($inoctets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCInOctets'] = $value['enterprises'];
}
foreach ($outoctets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCOutOctets'] = $value['enterprises'];
}
foreach ($inbpackets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCInBroadcastPkts'] = $value['enterprises'];
}
foreach ($outbpackets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCOutBroadcastPkts'] = $value['enterprises'];
}
foreach ($inmpackets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCInMulticastPkts'] = $value['enterprises'];
}
foreach ($outmpackets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCOutMulticastPkts'] = $value['enterprises'];
}
foreach ($inupackets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCInUcastPkts'] = $value['enterprises'];
}
foreach ($outupackets as $index => $value) {
    $index = preg_replace("/^(.*?)\.([0-9]+)$/", '$2', $index);
    $port_stats[$index]['ifHCOutUcastPkts'] = $value['enterprises'];
}

unset($inoctets);
unset($outoctets);
unset($inbpackets);
unset($outbpackets);
unset($inmpackets);
unset($outmpackets);
unset($inupackets);
unset($outupackets);
