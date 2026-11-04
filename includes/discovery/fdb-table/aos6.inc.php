<?php

use App\Facades\PortCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Mac;

/**
 * aos6.inc.php
 *
 * Discover FDB data with ALCATEL-IND1-MAC-ADDRESS-MIB
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
 * @link      https://www.librenms.org
 *
 * @copyright LibreNMS contributors
 * @author    Tony Murray <murraytony@gmail.com>
 * @author    JoseUPV
 * @author    Paul Iercosan <mail@paulierco.ro>
 */
// slMacAddressTable is indexed by ifIndex, tables passed in (aos7) may use bridge ports.
$fdb_ports_are_ifindex = false;

if (empty($fdbPort_table)) {
    $dot1d = SnmpQuery::mibDir('nokia/aos6')
        ->walk('ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition')
        ->table(3);

    if (! empty($dot1d)) {
        echo 'AOS6 MAC-ADDRESS-MIB: ';
        $fdbPort_table = [];
        $fdb_ports_are_ifindex = true;

        foreach ($dot1d as $portLocal => $data) {
            foreach ($data as $vlanLocal => $data2) {
                if (! isset($fdbPort_table[$vlanLocal]['dot1qTpFdbPort'])) {
                    $fdbPort_table[$vlanLocal] = ['dot1qTpFdbPort' => []];
                }

                foreach ($data2 as $macLocal => $entry) {
                    $fdbPort_table[$vlanLocal]['dot1qTpFdbPort'][$macLocal] = (int) $portLocal;
                }
            }
        }
    }
}

if (! empty($fdbPort_table)) {
    $device_id = $device['device_id'];

    // Map physical LAG members using data discovered by the ports-stack module.
    // Only rows whose parent is a LAG interface are used, other stacked interfaces are not remapped.
    $lag_ports = DB::table('ports_stack')
        ->join('ports', 'ports.port_id', '=', 'ports_stack.low_port_id')
        ->where('ports_stack.device_id', $device_id)
        ->where('ports.ifType', 'ieee8023adLag')
        ->where('ifStackStatus', 'active')
        ->where('high_ifIndex', '>', 0)
        ->where('low_ifIndex', '>', 0)
        ->pluck('low_ifIndex', 'high_ifIndex')
        ->mapWithKeys(fn ($parent, $child) => [(int) $child => (int) $parent])
        ->all();

    // Build dot1dBasePort to port_id dictionary.
    // Not used when the table holds ifIndexes: bridge port numbers overlap with them (slot 9 port 1 is bridge port 1025).
    $portid_dict = [];
    $dot1dBasePortIfIndex = $fdb_ports_are_ifindex
        ? []
        : SnmpQuery::walk('BRIDGE-MIB::dot1dBasePortIfIndex')->valuesByIndex();

    foreach ($dot1dBasePortIfIndex as $portLocal => $data) {
        $ifIndex = (int) ($data['BRIDGE-MIB::dot1dBasePortIfIndex'] ?? 0);

        if (! $ifIndex) {
            continue;
        }

        $ifIndex = $lag_ports[$ifIndex] ?? $ifIndex;

        $portid_dict[(int) $portLocal] = PortCache::getIdFromIfIndex(
            $ifIndex,
            $device_id
        );
    }

    /*
     * AOS6 may report an ifIndex directly instead of a bridge port.
     * Aggregate bridge ports start at 4098 and map to 40000001, etc.
     */
    foreach ($fdbPort_table as $data) {
        foreach ($data['dot1qTpFdbPort'] as $portLocal) {
            $portLocal = (int) $portLocal;

            if (isset($portid_dict[$portLocal])) {
                continue;
            }

            if ($portLocal >= 40000000) {
                $ifIndex = $portLocal;
            } elseif ($portLocal >= 4098 && $portLocal < 5000) {
                $ifIndex = 40000000 + ($portLocal - 4097);
            } else {
                $ifIndex = $lag_ports[$portLocal] ?? $portLocal;
            }

            $port_id = PortCache::getIdFromIfIndex($ifIndex, $device_id);

            if ($port_id) {
                $portid_dict[$portLocal] = $port_id;
            }
        }
    }

    // Collect data and populate $insert.
    foreach ($fdbPort_table as $vlan => $data) {
        foreach ($data['dot1qTpFdbPort'] as $mac => $portLocal) {
            $portLocal = (int) $portLocal;

            if ($portLocal === 0) {
                Log::debug("No port known for $mac\n");

                continue;
            }

            if (! isset($portid_dict[$portLocal])) {
                Log::debug("No port mapping for bridge port $portLocal\n");

                continue;
            }

            $mac_address = Mac::parse($mac)->hex();

            if (strlen($mac_address) != 12) {
                Log::debug("MAC address padding failed for $mac\n");

                continue;
            }

            $vlan_id = $vlans_dict[$vlan] ?? 0;
            $port_id = $portid_dict[$portLocal];
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;

            Log::debug("vlan $vlan_id mac $mac_address port ($portLocal) $port_id\n");
        }
    }
}
echo PHP_EOL;
