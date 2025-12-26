<?php

/**
 * timos.inc.php
 *
 * Discover FDB data with TIMETRA-SERV-MIB for Nokia TiMOS devices
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
 * @copyright  2025 LibreNMS Contributors
 * @author     LibreNMS Contributors
 */

use App\Facades\PortCache;
use App\Models\Vlan;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Mac;

/*
 * Nokia TiMOS devices use TIMETRA-SERV-MIB::tlsFdbInfoTable for FDB information.
 *
 * The table is indexed by:
 *   - svcId (service ID)
 *   - tlsFdbMacAddr (MAC address)
 *
 * Key OIDs:
 *   - tlsFdbMacAddr: The MAC address
 *   - tlsFdbLocale: Where the MAC is located (sap, sdp, cpm, endpoint, vxlan, evpnMpls, blackhole)
 *   - tlsFdbPortId: The port ID when tlsFdbLocale is 'sap' (this is the TmnxPortID which equals ifIndex)
 *   - tlsFdbEncapValue: The encapsulation value (encoded per TIMETRA-TC-MIB::TmnxEncapVal)
 *
 * TmnxEncapVal encoding (32-bit):
 *   - nullEncap: 0
 *   - dot1qEncap: lower 12 bits = VLAN ID (0x0FFF mask)
 *   - qinqEncap: lower 16 bits = outer VLAN, upper 16 bits = inner VLAN
 *
 * We only process entries where tlsFdbLocale is 'sap' (1) as these are the local learned MACs.
 *
 * Nokia SAP identifier format: ServiceID:PortId:EncapValue (e.g., 100:1/1/1:500)
 */

/**
 * Decode TmnxEncapVal to extract VLAN ID(s)
 *
 * @param  int|string  $encapVal  The encoded encapsulation value
 * @return array Array with 'outer' and optionally 'inner' VLAN IDs
 *
 * @see TIMETRA-TC-MIB::TmnxEncapVal
 */
function decodeNokiaEncapValue($encapVal): array
{
    $encapVal = (int) $encapVal;

    // Null encapsulation
    if ($encapVal == 0) {
        return ['outer' => 0, 'inner' => null];
    }

    // Check for QinQ: if upper 16 bits have a value (ignoring special bits)
    $innerVlan = ($encapVal >> 16) & 0x0FFF;  // Upper 12 bits of upper 16 bits
    $outerVlan = $encapVal & 0x0FFF;          // Lower 12 bits

    if ($innerVlan > 0) {
        // QinQ encapsulation
        return ['outer' => $outerVlan, 'inner' => $innerVlan];
    }

    // Simple dot1q encapsulation - VLAN is in lower 12 bits
    return ['outer' => $outerVlan, 'inner' => null];
}

/**
 * Format TmnxEncapVal for display (Nokia-friendly format)
 *
 * @param  int|string  $encapVal  The encoded encapsulation value
 * @return string Formatted encap value (e.g., "500" or "100.200" for QinQ)
 */
function formatNokiaEncapValue($encapVal): string
{
    $decoded = decodeNokiaEncapValue($encapVal);

    if ($decoded['inner'] !== null) {
        // QinQ format: outer.inner
        return $decoded['outer'] . '.' . $decoded['inner'];
    }

    if ($decoded['outer'] == 4095) {
        return '*';  // Wildcard
    }

    return (string) $decoded['outer'];
}

// Walk only the required FDB columns for best performance
// Testing showed: 5 columns = 388s, 3 columns = 144s, full table entry = 10+ min
// The selective approach is fastest because tlsFdbInfoEntry has 25+ columns we don't need
$fdbTable = SnmpQuery::hideMib()->walk([
    'TIMETRA-SERV-MIB::tlsFdbLocale',
    'TIMETRA-SERV-MIB::tlsFdbPortId',
    'TIMETRA-SERV-MIB::tlsFdbEncapValue',
])->table(2);

if (! empty($fdbTable)) {
    // Count SAP entries for progress indication
    $sapCount = 0;
    foreach ($fdbTable as $svcId => $macEntries) {
        foreach ($macEntries as $macIndex => $entry) {
            $locale = $entry['tlsFdbLocale'] ?? null;
            if ($locale === 'sap' || $locale === '1' || $locale === 1) {
                $sapCount++;
            }
        }
    }
    echo "TIMETRA-SERV-MIB: $sapCount SAP entries" . PHP_EOL;

    foreach ($fdbTable as $svcId => $macEntries) {
        foreach ($macEntries as $macIndex => $entry) {
            // Only process entries with tlsFdbLocale = 'sap' (1)
            // sap(1), sdp(2), cpm(3), endpoint(4), vxlan(5), evpnMpls(6), blackhole(7)
            $locale = $entry['tlsFdbLocale'] ?? null;
            if ($locale !== 'sap' && $locale !== '1' && $locale !== 1) {
                continue;
            }

            // Get the port ID (this is the TmnxPortID which is the same as ifIndex for physical ports)
            $portId = $entry['tlsFdbPortId'] ?? null;
            if (empty($portId) || $portId == 0) {
                Log::debug("Skipping MAC $macIndex in svc $svcId - no valid port ID\n");
                continue;
            }

            // Get the encapsulation value (VLAN ID)
            $encapValue = $entry['tlsFdbEncapValue'] ?? 0;

            // Parse the MAC address - the index format contains the MAC address
            // Format: svcId.macAddr where macAddr is 6 octets separated by dots
            $mac_address = Mac::parse($macIndex)->hex();
            if (strlen($mac_address) != 12) {
                Log::debug("MAC address parsing failed for $macIndex\n");
                continue;
            }

            // Get the port_id from the ifIndex (TmnxPortID equals ifIndex for physical ports)
            $port_id = PortCache::getIdFromIfIndex($portId, $device['device_id']);

            if (! $port_id) {
                Log::debug("Could not find port for TmnxPortID $portId (MAC: $mac_address)\n");
                continue;
            }

            // Decode the encapsulation value to get VLAN ID(s)
            // TmnxEncapVal is encoded: dot1q uses lower 12 bits, QinQ uses upper/lower 16 bits
            $decodedEncap = decodeNokiaEncapValue($encapValue);
            $vlanNumber = $decodedEncap['outer'];  // Use outer VLAN for FDB lookup

            // Skip if no valid VLAN (null encap or wildcard)
            if ($vlanNumber == 0 || $vlanNumber == 4095) {
                Log::debug("Skipping MAC $mac_address - no valid VLAN (encap: $encapValue, decoded: $vlanNumber)\n");
                continue;
            }

            // Create VLAN if it doesn't exist in the database (similar to Cisco IOS approach)
            if (! array_key_exists($vlanNumber, $vlans_dict)) {
                $vlanName = 'VLAN ' . $vlanNumber;
                // For QinQ, include inner VLAN in the name
                if ($decodedEncap['inner'] !== null) {
                    $vlanName = "VLAN $vlanNumber (QinQ inner: {$decodedEncap['inner']})";
                }

                $vlan = Vlan::create([
                    'device_id' => $device['device_id'],
                    'vlan_vlan' => $vlanNumber,
                    'vlan_name' => $vlanName,
                ]);
                $vlans_dict[$vlanNumber] = $vlan->vlan_id;
                Log::debug("Created VLAN $vlanNumber (id: {$vlan->vlan_id})\n");
            }

            $vlan_id = $vlans_dict[$vlanNumber];

            // Nokia SAP format: ServiceID:Port:EncapValue (formatted for display)
            $formattedEncap = formatNokiaEncapValue($encapValue);
            $sapIdentifier = "$svcId:$portId:$formattedEncap";

            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            Log::debug("SAP $sapIdentifier mac $mac_address vlan $vlanNumber port ($portId) $port_id\n");
        }
    }
}

unset($fdbTable);
