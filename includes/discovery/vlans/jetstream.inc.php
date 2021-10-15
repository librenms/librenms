<?php
/**
 * jetstream.inc.php
 *
 * LibreNMS vlan discovery module for Jetstream TPLINK
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
 * @author     peca.nesovanovic@sattrakt.com
 * @author     mtammasss@gmail.com
 * @author     PipoCanaja
 */

// first release by (peca.nesovanovic@sattrakt.com) # 2020/05/25
// jetstreamExpand function by Molnar Tamas (mtammasss@gmail.com) # 2020/05/25
//
// tested on: T1600G-28TS 3.0; T2600G-18TS 2.0;
//
// todo: detect LAG ports ??? now parser assume that there is no LAG port
// 2021-06-07: Added Vlan parsing on LAG ports
//
//SNMP input example STRING: '1/0/1-2,1/0/4-6,1/0/25,LAG1-3,LAG5'

if (! function_exists('jetstreamExpand')) {
    function jetstreamExpand($var)
    {
        $result = [];

        preg_match_all('#(LAG|\d+/\d+/)(\d+)(?:-(\d+))?#', $var, $lags);

        foreach ($lags[2] as $index => $start) {
            $end = $lags[3][$index] ?: $start;
            for ($i = $start; $i <= $end; $i++) {
                $result[] = $lags[1][$index] . $i; //need to be in form LAGx or 1/0/x
            }
        }

        return $result;
    }
}

echo 'Jetstream VLANs: ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    d_echo("\n $vlanversion \n");
    $vtpdomain_id = 1;

    $jet_vlanDb = snmpwalk_cache_oid($device, 'vlanConfigEntry', [], 'TPLINK-DOT1Q-VLAN-MIB');
    $jet_portMapping = snmpwalk_cache_oid($device, 'vlanPortConfigTable', [], 'TPLINK-DOT1Q-VLAN-MIB');
    foreach ($jet_portMapping as $jet_ifindex => $jet_port) {
        $jet_stringPortMapping[$jet_port['vlanPortNumber']] = $jet_port;
        $jet_stringPortMapping[$jet_port['vlanPortNumber']]['ifindex'] = $jet_ifindex;
    }
    foreach ($jet_vlanDb as $jet_vlan_id => $jet_vlan_data) {
        d_echo(" $jet_vlan_id ");

        if (is_array($vlans_db[$vtpdomain_id][$jet_vlan_id])) {
            $vlan_data = $vlans_db[$vtpdomain_id][$jet_vlan_id];

            if ($vlan_data['vlan_name'] != $jet_vlan_data['dot1qVlanDescription']) {
                $vlan_upd['vlan_name'] = $jet_vlan_data['dot1qVlanDescription'];
                dbUpdate($vlan_upd, 'vlans', '`vlan_id` = ?', [$vlan_data['vlan_id']]);
                log_event("VLAN $vlan_id changed name {$vlan_data['vlan_name']} -> " . $jet_vlan_data['dot1qVlanDescription'], $device, 'vlan');
                echo 'U';
            } else {
                echo '.';
            }
        } else {
            dbInsert([
                'device_id' => $device['device_id'],
                'vlan_domain' => $vtpdomain_id,
                'vlan_vlan' => $jet_vlan_id,
                'vlan_name' => $jet_vlan_data['dot1qVlanDescription'],
                'vlan_type' => ['NULL'],
            ], 'vlans');

            log_event('VLAN added: ' . $jet_vlan_data['dot1qVlanDescription'] . ", $vlan_id", $device, 'vlan');
            echo '+';
        }
        $device['vlans'][$vtpdomain_id][$jet_vlan_id] = $jet_vlan_id;

        foreach (jetstreamExpand($jet_vlan_data['vlanTagPortMemberAdd']) as $port_nr) {
            if (isset($jet_stringPortMapping[$port_nr])) {
                d_echo("ID: $jet_vlan_id -> PORT: " . $port_nr . ', ifindex: ' . $jet_stringPortMapping[$port_nr]['ifindex'] . " \n");
                $per_vlan_data[$jet_vlan_id][$jet_stringPortMapping[$port_nr]['ifindex']]['untagged'] = 0;
            }
        }
        foreach (jetstreamExpand($jet_vlan_data['vlanUntagPortMemberAdd']) as $port_nr) {
            if (isset($jet_stringPortMapping[$port_nr])) {
                d_echo("ID: $jet_vlan_id -> PORT: " . $port_nr . ', ifindex: ' . $jet_stringPortMapping[$port_nr]['ifindex'] . " \n");
                $per_vlan_data[$jet_vlan_id][$jet_stringPortMapping[$port_nr]['ifindex']]['untagged'] = 1;
            }
        }
    }
}
