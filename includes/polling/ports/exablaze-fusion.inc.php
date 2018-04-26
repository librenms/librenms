<?php
/**
* exablaze-fusion.inc.php
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
* @package    LibreNMS
* @author     Nash Kaminski <Nash.Kaminski@gs.com>
*/

$exa_stats = snmpwalk_cache_oid($device, 'fusionPortTable', array(), 'EXALINK-FUSION-MIB');
unset($exa_stats[0]);

#ifName is the key
$required = array(
    'ifHighSpeed' => 'fusionPortSpeed',
    'ifHCInOctets' => 'fusionPortRXBytes',
    'ifHCOutOctets' => 'fusionPortTXBytes',
    'ifConnectorPresent' => 'fusionPortPresent',
    'ifOperStatus' => 'fusionPortHasSignal',
    'ifAdminStatus' => 'fusionPortEnabled',
    'ifAlias' => 'fusionPortName',
);

$orig_tf = array('true', 'false');
$std_tf = array('up','down');

foreach ($exa_stats as $name => $tmp_stats) {
    if ($exa_stats[$name]['fusionPortPresent'] === 2) {
        continue;
    }
    $e_name = explode('.', $name);
    $index = (((int)($e_name[0]))-1)*16 + (int)($e_name[1]);
    $port_stats[$index] = $tmp_stats;
    $port_stats[$index]['ifName'] = $name;
    foreach ($required as $ifEntry => $IfxStat) {
        $port_stats[$index][$ifEntry] = str_replace($orig_tf, $std_tf, $exa_stats[$name][$IfxStat]);
    }
    $port_stats[$index]['ifDescr'] = $port_stats[$index]['ifAlias'];
}
