<?php
/**
 * routeros.inc.php.
 *
 * LibreNMS vlan discovery module for Mikrotik RouterOS
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
 */

// Discovery module using ROS ability to start script execution through SNMP.
// This way, script called "LNMS_vlans" could start and send data about vlans on device
// data format is: type,vlanId,ifName <cr>
// i.e: T,254,ether1 is translated to: tagged vlan 254 on ether1

$oids = snmp_walk($device, '.1.3.6.1.4.1.14988.1.1.8.1.1.2', '-Osqn', '');
$oids = trim($oids);

if ($oids) {
    echo 'Mikrotik VLANs '."\n";

    foreach (explode("\n", $oids) as $data) {
        if ($data) {
            $split = trim(explode(' ', $data)[0]);
            $value = trim(explode(' ', $data)[1]);
            $si = explode('.', $split)[14]; //Script Index
	    // Script name is "LNMS_vlans"
            if ($value == 'LNMS_vlans') {
                $sIndex = $si;
            }
        }
    }
}

if (isset($sIndex)) {
    d_echo('Mikrotik script found');
    $vlanversion = 1;
    $data = snmp_get($device, '.1.3.6.1.4.1.14988.1.1.18.1.1.2.'.$sIndex, '-Ovq', '');
    $data = trim($data);
    $oldId = 0;
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
        $vType = trim(explode(',', $line)[0]);
        $vId = trim(explode(',', $line)[1]);
        $vIf = trim(explode(',', $line)[2]);
        $vName = 'Vlan_'.$vId;
        if ($oldId != $vId) {
            $oldId = $vId;
            $device['vlans'][1][$vId] = $vId;

            $old_data = dbFetchRow('select * FROM vlans where `device_id` = ? AND `vlan_vlan` = ? AND `vlan_domain` = ?', [$device['device_id'], $vId, 1]);
            if (isset($old_data)) {
                if ($old_data['vlan_name'] != $vName) {
                    $vlan_upd['vlan_name'] = $vName;
                    dbUpdate($vlan_upd, 'vlans', '`vlan_id` = ?', [$vId]);
                    echo 'U';
                } else {
                    echo '.';
                }
            } else {
                dbInsert([
                    'device_id'   => $device['device_id'],
                    'vlan_domain' => 1,
                    'vlan_vlan'   => $vId,
                    'vlan_name'   => $vName,
                    'vlan_type'   => ['NULL'],
                ], 'vlans');
                echo '+';
            }
        }
        $port = dbFetchRow('select * FROM ports WHERE `device_id` = ? AND `ifName` = ?', [$device['device_id'], $vIf]);
        $ifIndex = $port['ifIndex'];
        d_echo("\n ifIndex from DB: $ifIndex \n");
        if ($vType == 'U') {
            $per_vlan_data[$vId][$ifIndex]['untagged'] = 1;
        } else {
            $per_vlan_data[$vId][$ifIndex]['untagged'] = 0;
        }
    }
}
