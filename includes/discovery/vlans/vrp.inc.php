<?php
/**
 * vrp.inc.php
 *
 * Discover VLANs on VRP os
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
 * @copyright  2024 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use App\Models\Eventlog;
use App\Models\Vlan;
use LibreNMS\Enum\Severity;
use Log;

echo 'HUAWEI VRP VLANs: ';

$vlans = SnmpQuery::hideMib()->walk('HUAWEI-L2VLAN-MIB::hwL2VlanDescr')->table(1);
$vlans = SnmpQuery::hideMib()->walk('HUAWEI-L2VLAN-MIB::hwL2VlanRowStatus')->table(1, $vlans);
$vlans = SnmpQuery::hideMib()->walk('HUAWEI-L2VLAN-MIB::hwL2VlanType')->table(1, $vlans);

if (! empty($vlans)) {
    $vtpdomainId = '1';
    foreach ($vlans as $vlanId => $vlanData) {
        if (in_array($vlanData['hwL2VlanRowStatus'], [1, 4, 5])) { // active(1), createAndGo(4), createAndWait(5)
            Log::debug('Processing vlan ID: ' . $vlanId);
            $vlan_name = $vlanData['hwL2VlanDescr'] ?? 'Vlan_' . $vlanId;

            $vlanDB = Vlan::updateOrCreate([
                'device_id' => $device['device_id'],
                'vlan_vlan' => $vlanId,
            ], [
                'vlan_domain' => $vtpdomainId,
                'vlan_name' => $vlan_name,
                'vlan_type' => $vlanData['hwL2VlanType'],
            ]);

            if (! $vlanDB->wasRecentlyCreated && $vlanDB->wasChanged()) {
                Eventlog::log("Vlan changed: ID -> $vlanId, NAME -> $vlan_name", $device['device_id'], 'vlan', Severity::Warning);
            }

            if ($vlanDB->wasRecentlyCreated) {
                Eventlog::log("Vlan added: ID -> $vlanId, NAME -> $vlan_name ", $device['device_id'], 'vlan', Severity::Warning);
            }

            $device['vlans'][$vtpdomainId][$vlanId] = $vlanId; //populate device['vlans'] with ID's
        }
    }
    $maxVlanId = max(array_keys($device['vlans'][$vtpdomainId])); //maximum nr of vlanid
    Log::debug('MaxVlanId: ' . $maxVlanId);

    $portsIndexes = SnmpQuery::hideMib()->walk('HUAWEI-L2IF-MIB::hwL2IfPortIfIndex')->table(1);
    $portsData = SnmpQuery::hideMib()->walk('HUAWEI-L2IF-MIB::hwL2IfTrunkAllowPassVlanListLow')->table(1);
    $portsData = SnmpQuery::hideMib()->walk('HUAWEI-L2IF-MIB::hwL2IfHybridTaggedVlanListLow')->table(1, $portsData);
    // high table
    if ($maxVlanId > 2047) {
        $portsData = SnmpQuery::hideMib()->walk('HUAWEI-L2IF-MIB::hwL2IfTrunkAllowPassVlanListHigh')->table(1, $portsData);
        $portsData = SnmpQuery::hideMib()->walk('HUAWEI-L2IF-MIB::hwL2IfHybridTaggedVlanListHigh')->table(1, $portsData);
    }
    if (! empty($portsData)) {
        foreach ($portsData as $pdIndex => $vlanData) {
            if (! empty($portsIndexes[$pdIndex])) {
                $ifIndex = $portsIndexes[$pdIndex]['hwL2IfPortIfIndex'];
                foreach (['Low', 'High'] as $hilo) {
                    foreach (['TrunkAllowPass', 'HybridTagged'] as $pType) {
                        $oid = 'hwL2If' . $pType . 'VlanList' . $hilo;
                        if (! empty($vlanData[$oid])) {
                            $vlansOnPort = q_bridge_bits2indices($vlanData[$oid]);
                            foreach ($vlansOnPort as $vlanIdOnPort) {
                                $vlanIdOnPort = ($hilo == 'High') ? ($vlanIdOnPort + 2047) : ($vlanIdOnPort - 1);
                                if (! empty($device['vlans'][$vtpdomainId][$vlanIdOnPort])) {
                                    $per_vlan_data[$vlanIdOnPort][$ifIndex]['untagged'] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
echo PHP_EOL;
