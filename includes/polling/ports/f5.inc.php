<?php
/**
 * f5.inc.php
 *
 * LibreNMS F5 Ports include
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$f5_stats = snmpwalk_cache_oid($device, 'sysIfxStat', [], 'F5-BIGIP-SYSTEM-MIB');
unset($f5_stats[0]);

foreach ($ifmib_oids as $oid) {
    echo "$oid ";
    $tmp_port_stats = snmpwalk_cache_oid($device, $oid, $tmp_port_stats, 'IF-MIB', null, '-OQUst');
}

$required = [
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
];

foreach ($tmp_port_stats as $index => $tmp_stats) {
    $descr = $tmp_port_stats[$index]['ifDescr'];
    $port_stats[$index] = $tmp_stats;
    $port_stats[$index]['ifDescr'] = $tmp_stats['ifDescr'];
    foreach ($required as $ifEntry => $IfxStat) {
        $port_stats[$index][$ifEntry] = $f5_stats[$descr][$IfxStat];
    }
}
