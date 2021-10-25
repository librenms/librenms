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

use App\Models\Port;
use App\Models\Vlan;
use log;

$oids = snmp_walk($device, '.1.3.6.1.4.1.14988.1.1.8.1.1.2', '-Osqn', '');
$oids = trim($oids);

if ($oids) {
    echo 'Mikrotik VLANs ' . "\n";

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
    $data = snmp_get($device, '.1.3.6.1.4.1.14988.1.1.18.1.1.2.' . $sIndex, '-Ovq', '');
    $data = trim($data);
    $oldId = 0;

    foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
        $vType = trim(explode(',', $line)[0]);
        $vId = trim(explode(',', $line)[1]);
        $vIf = trim(explode(',', $line)[2]);
        $vName = 'Vlan_' . $vId;

        if ($oldId != $vId) {
            $oldId = $vId;

            //add vlan ID to $device array
            $device['vlans'][1][$vId] = $vId;

            //try to get existing data
            $old_data = Vlan::where('device_id', $device['device_id'])->where('vlan_vlan', $vId)->get()->first->toArray();

            if (isset($old_data)) {
                if ($old_data->vlan_name != $vName) {
                    Vlan::where('device_id', $device['device_id'])->where('vlan_vlan', $vId)->update(['vlan_name'=>$vName]);
                    Log::event('Vlan id: ' . $vId . ' changed name to: ' . $vName, $device['device_id'], 'vlan', 4);
                }
            } else {
                //vlan does not exist, create new entry
                $new_data = new Vlan();
                $new_data->device_id = $device['device_id'];
                $new_data->vlan_domain = 1;
                $new_data->vlan_vlan = $vId;
                $new_data->vlan_name = $vName;
                $new_data->save();
                Log::event('Vlan id: ' . $vId . ' added', $device['device_id'], 'vlan', 4);
            }
        }

        //find ifIndex connected to ifName on current device
        $port = Port::where('device_id', $device['device_id'])->where('ifName', $vIf)->get()->first->toArray();
        $ifIndex = $port->ifIndex;
        d_echo("\n ifIndex from DB: $ifIndex \n");

        //populate per_vlan_data
        if ($vType == 'U') {
            $per_vlan_data[$vId][$ifIndex]['untagged'] = 1;
        } else {
            $per_vlan_data[$vId][$ifIndex]['untagged'] = 0;
        }
    }
}
