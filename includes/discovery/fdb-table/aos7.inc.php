<?php

/**
 * aos7.inc.php
 *
 * Discover FDB data with ALCATEL-IND1-MAC-ADDRESS-MIB (AOS7+)
 * Uses the OID Index (slMacAddressGblMapping) to map MACs to Ports/LAGs correctly.
 * *
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
 * @link      https://www.librenms.org
 *
 * @copyright LibreNMS contributors
 * @author    Tony Murray <murraytony@gmail.com>
 * @author    JoseUPV
 * @author    Paul Iercosan <mail@paulierco.ro>
 */

use App\Facades\PortCache;
use LibreNMS\Util\Mac;

echo 'AOS7+ MAC-ADDRESS-MIB: ';

// IMPORTANT: $vlans_dict is provided by includes/discovery/fdb-table.inc.php and maps vlan_vlan -> vlan_id
$vlans_dict = (isset($vlans_dict) && is_array($vlans_dict)) ? $vlans_dict : [];

// The structure is slMacAddressGblManagement[Type][FID][Mapping][Vlan][TimeMark][Mac] = BasePort
// Key index 3 ('Mapping') corresponds to the ifIndex of the Port or LinkAggregate
$dot1d = snmpwalk_group($device, 'slMacAddressGblManagement', 'ALCATEL-IND1-MAC-ADDRESS-MIB', 0, [], 'nokia/aos7');

if (! empty($dot1d['slMacAddressGblManagement'])) {
    foreach ($dot1d['slMacAddressGblManagement'] as $type => $fids) {
        if (! is_array($fids)) {
            continue;
        }

        foreach ($fids as $fid => $mappings) {
            if (! is_array($mappings)) {
                continue;
            }

            foreach ($mappings as $ifIndex => $vlans) {
                // $ifIndex here is the correct interface (e.g. 40000001 for LinkAgg)
                if (! is_array($vlans)) {
                    continue;
                }

                // Resolve the Port ID from LibreNMS database using this ifIndex
                $port_id = PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

                // If we can't find a port for this index, skip these MACs
                if (! $port_id) {
                    continue;
                }

                foreach ($vlans as $vlan_id => $timeMarks) {
                    if (! is_array($timeMarks)) {
                        continue;
                    }

                    $vlan_vlan = (int) $vlan_id;

                    // Map VLAN tag (vlan_vlan) -> LibreNMS DB vlan_id
                    $vlan_id = $vlans_dict[$vlan_vlan] ?? 0;
                    if (! $vlan_id) {
                        continue;
                    }

                    foreach ($timeMarks as $timeMark => $macs) {
                        if (! is_array($macs)) {
                            continue;
                        }

                        foreach ($macs as $mac => $basePort_value) {
                            // We ignore $basePort_value because it points to the physical member
                            // instead of the Logical LinkAgg. We use $port_id derived from $ifIndex above.

                            $mac_address = Mac::parse($mac)->hex();
                            if (strlen($mac_address) === 12) {
                                // Add to the $insert array. LibreNMS core uses this to update the DB.
                                $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
                            }
                        }
                    }
                }
            }
        }
    }
}

// We do NOT include aos6.inc.php anymore, as we have handled the logic specifically for AOS7 above.
