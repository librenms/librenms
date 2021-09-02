<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage discovery
 * @link       https://www.librenms.org
 * @copyright  2018 PipoCanaja <pipocanaja@gmail.com>
 * @author     PipoCanaja <pipocanaja@gmail.com>
 * @author     Tony Murray <murraytony@gmail.com> (bridge.inc.php used as base)
 */

$fdbPort_table = snmpwalk_group($device, 'hwDynFdbPort', 'HUAWEI-L2MAM-MIB');
$hwCfgMacAddrQueryIfIndex = snmpwalk_group($device, 'hwCfgMacAddrQueryIfIndex', 'HUAWEI-L2MAM-MIB', 10);

if (! empty($fdbPort_table)) {
    echo 'HUAWEI-L2MAM-MIB:' . PHP_EOL;
    $data_oid = 'hwDynFdbPort';
    // Collect data and populate $insert
    foreach ($fdbPort_table as $mac => $data) {
        foreach ($data[$data_oid] as $vlan => $basePort) {
            $ifIndex = reset($basePort); // $baseport can be ['' => '119'] or ['0' => '119']
            if (! $ifIndex) {
                continue;
            }
            $port = get_port_by_index_cache($device['device_id'], $ifIndex);
            $port_id = $port['port_id'];
            $mac_address = implode(array_map('zeropad', explode(':', $mac)));
            if (strlen($mac_address) != 12) {
                d_echo("MAC address padding failed for $mac\n");
                continue;
            }
            $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan mac $mac_address port ($ifIndex) $port_id\n");
        }
    }
}

// Static (sticky) mac addresses are not stored in the same table.
if (! empty($hwCfgMacAddrQueryIfIndex)) {
    echo 'HUAWEI-L2MAM-MIB (static):' . PHP_EOL;
    foreach ($hwCfgMacAddrQueryIfIndex as $vlan => $data) {
        if (! empty($data[0][0][0])) {
            foreach ($data[0][0][0] as $mac => $data_next) {
                if (! empty($data_next['showall'][0][0][0][0]['hwCfgMacAddrQueryIfIndex'])) {
                    $basePort = $data_next['showall'][0][0][0][0]['hwCfgMacAddrQueryIfIndex'];
                    $ifIndex = reset($basePort);
                    if (! $ifIndex) {
                        continue;
                    }
                    $port = get_port_by_index_cache($device['device_id'], $ifIndex);
                    $port_id = $port['port_id'];
                    $mac_address = implode(array_map('zeropad', explode(':', $mac)));
                    if (strlen($mac_address) != 12) {
                        d_echo("MAC address padding failed for $mac\n");
                        continue;
                    }
                    $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;
                    $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
                    d_echo("vlan $vlan mac $mac_address port ($ifIndex) $port_id\n");
                }
            }
        }
    }
}

unset($fdbPort_table);
unset($hwCfgMacAddrQueryIfIndex);
