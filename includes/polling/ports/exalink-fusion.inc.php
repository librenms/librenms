<?php
/**
 * exalink-fusion.inc.php
 *
 * Copyright (C) 2018 Goldman Sachs & Co.
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
 * @author     Nash Kaminski <Nash.Kaminski@gs.com>
 */
$exa_stats = snmpwalk_cache_oid($device, 'fusionPortTable', [], 'EXALINK-FUSION-MIB');
unset($exa_stats[0]);

$obj_map = [
    'ifName' => 'fusionPortName',
    'ifAlias' => 'fusionPortAlias',
    'ifOperStatus' => 'fusionPortHasSignal',
    'ifAdminStatus' => 'fusionPortEnabled',
    'ifHighSpeed' => 'fusionPortSpeed',
    'ifHCInOctets' => 'fusionPortRXBytes',
    'ifHCOutOctets' => 'fusionPortTXBytes',
    'ifInErrors' => 'fusionPortRXErrors',
    'ifConnectorPresent' => 'fusionPortPresent',
];

// Rename these to use "up" and "down"
$tf_rename_map = [
    'fusionPortHasSignal',
    'fusionPortEnabled',
];
$orig_tf = ['true', 'false'];
$std_tf = ['up', 'down'];

// Only supports ethernet
$ifType = 'ethernetCsmacd';

foreach ($exa_stats as $name => $tmp_stats) {
    $e_name = explode('.', $name);
    $index = (((int) $e_name[0]) - 1) * 16 + (int) $e_name[1];
    $port_stats[$index] = [];
    $port_stats[$index]['ifName'] = $name;
    $port_stats[$index]['ifType'] = $ifType;
    foreach ($obj_map as $ifEntry => $IfxStat) {
        if (in_array($IfxStat, $tf_rename_map)) {
            $val = str_replace($orig_tf, $std_tf, $exa_stats[$name][$IfxStat]);
        } else {
            $val = $exa_stats[$name][$IfxStat];
        }
        $port_stats[$index][$ifEntry] = $val;
    }
    $port_stats[$index]['ifDescr'] = $port_stats[$index]['ifName'];
}
