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

use App\Models\Vlan;

$scripts = SnmpQuery::walk('MIKROTIK-MIB::mtxrScriptName')->table();
$sIndex = array_flip($scripts['MIKROTIK-MIB::mtxrScriptName'] ?? [])['LNMS_vlans'] ?? null;

if (isset($sIndex)) {
    echo "Mikrotik VLANs \n";
    $vlanversion = 1;
    $data = SnmpQuery::get('MIKROTIK-MIB::mtxrScriptRunOutput.' . $sIndex)->value();
    $ifNames = array_flip($os->getCacheByIndex('ifName', 'IF-MIB'));
    $oldId = 0;

    foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
        [$vType, $vId, $vIf] = array_map('trim', explode(',', $line));
        $vName = 'Vlan_' . $vId;

        if ($oldId != $vId) {
            $oldId = $vId;

            //add vlan ID to $device array
            $device['vlans'][1][$vId] = $vId;

            //try to get existing data
            $vlan = Vlan::firstOrNew([
                'device_id' => $device['device_id'],
                'vlan_vlan' => $vId,
            ], [
                'vlan_domain' => 1,
                'vlan_name' => $vName,
            ]);

            if ($vlan->isDirty('vlan_name')) {
                Log::event("Vlan id: $vId changed name to: $vName from " . $vlan->getOriginal('vlan_name'), $device['device_id'], 'vlan', 4);
            }

            if (! $vlan->exists) {
                Log::event("Vlan id: $vId: $vName added", $device['device_id'], 'vlan', 4);
            }

            $vlan->save();
        }

        //find ifIndex connected to ifName on current device

        $ifIndex = $ifNames[$vIf];
        d_echo("\n ifIndex from DB: $ifIndex \n");

        //populate per_vlan_data
        $per_vlan_data[$vId][$ifIndex]['untagged'] = $vType == 'U' ? 1 : 0;
    }
}
