<?php
/**
 * q-bridge-mib.inc.php
 *
 * LibreNMS vlan discovery module for Q-BRIDGE-MIB
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

use App\Models\Vlan;

echo 'IEEE8021-Q-BRIDGE-MIB VLANs: ';

$vlanversion = snmp_get($device, 'dot1qVlanVersionNumber.0', '-Oqv', 'IEEE8021-Q-BRIDGE-MIB');

if ($vlanversion == 'version1' || $vlanversion == '2') {
    echo "ver $vlanversion ";

    $vtpdomain_id = '1';

    //get vlan names
    $vlans = snmpwalk_cache_oid($device, 'dot1qVlanStaticName', [], 'Q-BRIDGE-MIB');

    //iterate through 'current' then 'static' dot1qVlan table
    foreach (['Current', 'Static'] as $table) {
        $oidsu = trim(snmp_walk($device, 'dot1qVlan' . $table . 'UntaggedPorts', ['-OsQ', '--hexOutputLength=0'], 'Q-BRIDGE-MIB'));
        $oidsm = trim(snmp_walk($device, 'dot1qVlan' . $table . 'EgressPorts', ['-OsQ', '--hexOutputLength=0'], 'Q-BRIDGE-MIB'));

        //vlan untagged ports
        if ($oidsu) {
            foreach (explode("\n", $oidsu) as $datau) {
                $dots = max(array_keys(explode('.', $datau))); //last dot is index
                $index = trim(explode('=', explode('.', $datau)[$dots])[0]); //vlan index
                $portsu[$index] = trim(explode('=', $datau)[1]); //untagged ports
            }
        }

        //vlan member ports (might be tagged)
        if ($oidsm) {
            foreach (explode("\n", $oidsm) as $datam) {
                $dots = max(array_keys(explode('.', $datam))); //last dot is index
                $index = trim(explode('=', explode('.', $datam)[$dots])[0]); //vlan index
                $portsm[$index] = trim(explode('=', $datam)[1]); //member ports (might be tagged)

                //check for vlan name and assign generic value if name does not exist
                if (! $vlans[$index]['dot1qVlanStaticName']) {
                    $vlans[$index] = ['dot1qVlanStaticName' => 'Vlan_' . $index];
                    d_echo('Vlans: assigned generic Vlan_' . $index . ' name');
                }
            }
            break; //table exist, do not read next table [current, static]
        }
    } //foreach [current,static]

    foreach ($vlans as $vlan_id => $vlan) {
        d_echo('Processing vlan ID: ' . $vlan_id);
        $vlan_name = $vlan['dot1qVlanStaticName'];

        //try to get existing data from DB
        $vlanDB = Vlan::firstOrNew([
            'device_id' => $device['device_id'],
            'vlan_vlan' => $vlan_id,
        ], [
            'vlan_domain' => $vtpdomain_id,
            'vlan_name' => $vlan_name,
        ]);

        //vlan does not exist
        if (! $vlanDB->exists) {
            Log::event("Vlan added: $vlan_id with name $vlan_name ", $device['device_id'], 'vlan', 4);
        }

        if ($vlanDB->vlan_name != $vlan_name) {
            $vlanDB->vlan_name = $vlan_name;
            Log::event("Vlan changed: $vlan_id new name $vlan_name", $device['device_id'], 'vlan', 4);
        }

        $vlanDB->save();

        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id; //populate device['vlans'] with ID's

        $untagged_ids = q_bridge_bits2indices($portsu[$vlan_id]); //portmap for untagged ports
        $egress_ids = q_bridge_bits2indices($portsm[$vlan_id]); //portmap for members ports (might be tagged)
        foreach ($egress_ids as $port_id) {
            if (isset($base_to_index[$port_id])) {
                $ifIndex = $base_to_index[$port_id];
                $per_vlan_data[$vlan_id][$ifIndex]['untagged'] = (in_array($port_id, $untagged_ids) ? 1 : 0);
            }
        }
    }
}
echo PHP_EOL;
