<?php

use App\Facades\PortCache;
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
 *
 * Discover FDB data for Alcatel-Lucent AOS6.
 *
 * - Primary source for per-VLAN MACs: ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition
 * - Port resolution:
 * * BRIDGE-MIB::dot1dBasePortIfIndex for normal bridge ports
 * * Special handling for AOS6 LAG "phantom" bridge ports (e.g. 4098, 4100)
 * - Optional remap physical member -> parent LAG using IF-MIB::ifStackStatus
 */
$fdbPort_table ??= [];
$vlans_dict = (isset($vlans_dict) && is_array($vlans_dict)) ? $vlans_dict : [];

// Build per-VLAN FDB table (prefer vendor MIB for VLAN accuracy)
if (empty($fdbPort_table)) { // allow other OS scripts to pre-fill it
    /** @phpstan-ignore-next-line */
    $dot1d = snmpwalk_group($device, 'slMacAddressDisposition', 'ALCATEL-IND1-MAC-ADDRESS-MIB', 0, [], 'nokia/aos6');

    if (! empty($dot1d['slMacAddressDisposition']) && is_array($dot1d['slMacAddressDisposition'])) {
        echo 'AOS6 MAC-ADDRESS-MIB: ';
        foreach ($dot1d['slMacAddressDisposition'] as $portLocal => $by_vlan) {
            if (! is_array($by_vlan)) {
                continue;
            }
            foreach ($by_vlan as $vlanLocal => $by_mac) {
                if (! isset($fdbPort_table[$vlanLocal]['dot1qTpFdbPort'])) {
                    $fdbPort_table[$vlanLocal] = ['dot1qTpFdbPort' => []];
                }
                if (! is_array($by_mac)) {
                    continue;
                }
                foreach ($by_mac as $macLocal => $one) {
                    // store "bridge port" (or AOS6 pseudo port) per MAC, per VLAN
                    $fdbPort_table[$vlanLocal]['dot1qTpFdbPort'][$macLocal] = (int) $portLocal;
                }
            }
        }
        echo PHP_EOL;
    }
}

if (empty($fdbPort_table)) {
    // Nothing to do
    echo "AOS6 FDB: no data\n";
    echo PHP_EOL;

    return;
}

// --------------------------------------------------------------------
// Build LAG topology map (child ifIndex -> parent ifIndex)
// --------------------------------------------------------------------
$child_to_parent_lag = [];

/** @phpstan-ignore-next-line */
$stack_data = snmpwalk_group($device, 'ifStackStatus', 'IF-MIB');

if (! empty($stack_data['ifStackStatus']) && is_array($stack_data['ifStackStatus'])) {
    foreach ($stack_data['ifStackStatus'] as $parent_ifIndex => $children) {
        $parent_ifIndex = (int) $parent_ifIndex;

        // parent 0 is just the "top" of the stack; skip it
        if ($parent_ifIndex === 0 || ! is_array($children)) {
            continue;
        }

        foreach ($children as $child_ifIndex => $status) {
            if ((int) $status !== 1) {
                continue;
            }
            $child_to_parent_lag[(int) $child_ifIndex] = $parent_ifIndex;
        }
    }
}

// --------------------------------------------------------------------
// Build bridgePort -> ifIndex map
// --------------------------------------------------------------------
$bridge_to_ifindex = [];

/** @phpstan-ignore-next-line */
$bp_data = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');

if (! empty($bp_data) && is_array($bp_data)) {
    foreach ($bp_data as $bp => $val) {
        if (is_array($val) && isset($val['dot1dBasePortIfIndex'])) {
            $bridge_to_ifindex[(int) $bp] = (int) $val['dot1dBasePortIfIndex'];
        }
    }
}

// --------------------------------------------------------------------
// Helper to resolve basePort -> (ifIndex, port_id)
// --------------------------------------------------------------------
$resolvePort = function (int $basePort) use ($device, $bridge_to_ifindex, $child_to_parent_lag): array {
    $device_id = (int) $device['device_id'];

    $candidates = [];

    // 1) Normal path: bridge port -> ifIndex via BRIDGE-MIB
    if (isset($bridge_to_ifindex[$basePort])) {
        $ifIndex = (int) $bridge_to_ifindex[$basePort];

        // If physical ifIndex is part of a LAG, show the parent LAG
        if (isset($child_to_parent_lag[$ifIndex])) {
            $ifIndex = (int) $child_to_parent_lag[$ifIndex];
        }

        $candidates[] = $ifIndex;
    }

    // 2) AOS6 quirks: LAG/phantom bridge ports (e.g. 4098, 4100)
    // Try multiple observed ifIndex encodings and pick the first that exists in PortCache.
    if ($basePort >= 4096) {
        // Observed in your environment: 4098 -> 40000001
        $candidates[] = 40000000 + ($basePort - 4097);

        // Also observed as an ifIndex present in IF-MIB: e.g. 13604098
        $candidates[] = 13600000 + $basePort;
    }

    // 3) Last resort: treat basePort as ifIndex
    $candidates[] = $basePort;

    // de-dup + sanitize
    $candidates = array_values(array_unique(array_filter($candidates, static fn ($v) => is_int($v) && $v > 0)));

    foreach ($candidates as $cand) {
        $cand = (int) $cand;

        // If candidate itself is a physical port in a LAG, prefer parent LAG
        if (isset($child_to_parent_lag[$cand])) {
            $cand = (int) $child_to_parent_lag[$cand];
        }

        $port_id = PortCache::getIdFromIfIndex($cand, $device_id);
        if (! empty($port_id)) {
            return [$cand, (int) $port_id];
        }
    }

    return [0, 0];
};

// --------------------------------------------------------------------
// Populate $insert safely (DB vlan_id + non-empty port_id)
// --------------------------------------------------------------------
$count = 0;

foreach ($fdbPort_table as $vlan => $data) {
    if (! is_array($data) || empty($data['dot1qTpFdbPort']) || ! is_array($data['dot1qTpFdbPort'])) {
        continue;
    }

    $vlan_vlan = (int) $vlan;
    $vlan_id = (int) ($vlans_dict[$vlan_vlan] ?? 0);

    foreach ($data['dot1qTpFdbPort'] as $mac => $basePort) {
        $basePort = (int) $basePort;

        if ($basePort === 0) {
            continue;
        }

        try {
            $mac_address = Mac::parse($mac)->hex();
        } catch (Throwable) {
            continue;
        }

        if (strlen($mac_address) !== 12) {
            continue;
        }

        [$ifIndex, $port_id] = $resolvePort($basePort);

        // Critical: never write empty port_id (this is what broke discovery)
        if (empty($port_id)) {
            Log::debug("AOS6 FDB: unmapped basePort=$basePort vlan_vlan=$vlan_vlan mac=$mac_address\n");
            continue;
        }

        $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
        $count++;

        Log::debug("AOS6 FDB: vlan_id=$vlan_id vlan_vlan=$vlan_vlan mac=$mac_address basePort=$basePort ifIndex=$ifIndex port_id=$port_id\n");
    }
}

unset($fdbPort_table);

echo "AOS6 FDB: Processed $count entries.\n";
echo PHP_EOL;