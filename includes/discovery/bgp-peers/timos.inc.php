<?php

/**
 * timos.inc.php
 *
 * LibreNMS bgp_peers for Timos
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
 * @copyright  2020 LibreNMS Contributors
 * @author     LibreNMS Contributors
 */

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\IP;

if ($device['os'] == 'timos') {
    $bgpPeersCache = SnmpQuery::numericIndex()->walk('TIMETRA-BGP-MIB::tBgpPeerNgTable')->valuesByIndex();
    foreach ($bgpPeersCache as $key => $value) {
        $oid = explode('.', (string) $key);
        $vrfInstance = $oid[0];
        $address = implode('.', array_slice($oid, 3));
        if (strlen($address) > 15) {
            $address = IP::fromSnmpString($address)->compressed();
        }
        $bgpPeers[$vrfInstance][$address] = $value;
    }
    unset($bgpPeersCache);

    $vrfs = DeviceCache::getPrimary()->vrfs()->select('vrf_id', 'vrf_oid')->get();
    foreach ($vrfs as $vrf) {
        $map_vrf['byId'][$vrf['vrf_id']]['vrf_oid'] = $vrf['vrf_oid'];
        $map_vrf['byOid'][$vrf['vrf_oid']]['vrf_id'] = $vrf['vrf_id'];
    }

    foreach ($bgpPeers ?? [] as $vrfOid => $vrf) {
        $vrfId = $map_vrf['byOid'][$vrfOid]['vrf_id'] ?? null;

        d_echo($vrfId);

        foreach ($vrf as $address => $value) {
            $astext = \LibreNMS\Util\AutonomousSystem::get($value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'])->name();
            if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->exists()) {
                $peers = [
                    'device_id' => $device['device_id'],
                    'vrf_id' => $vrfId,
                    'bgpPeerIdentifier' => $address,
                    'bgpPeerRemoteAs' => $value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'],
                    'bgpPeerState' => 'idle',
                    'bgpPeerAdminStatus' => 'stop',
                    'bgpLocalAddr' => '0.0.0.0',
                    'bgpPeerRemoteAddr' => '0.0.0.0',
                    'bgpPeerInUpdates' => 0,
                    'bgpPeerOutUpdates' => 0,
                    'bgpPeerInTotalMessages' => 0,
                    'bgpPeerOutTotalMessages' => 0,
                    'bgpPeerFsmEstablishedTime' => 0,
                    'bgpPeerInUpdateElapsedTime' => 0,
                    'astext' => $astext,
                ];
                if (empty($vrfId)) {
                    unset($peers['vrf_id']);
                }

                $seenPeerID[] = DeviceCache::getPrimary()->bgppeers()->create($peers)->bgpPeer_id;

                if (LibrenmsConfig::get('autodiscovery.bgp')) {
                    $name = gethostbyaddr($address);
                    discover_new_device($name, $device, 'BGP');
                }
                echo '+';
            } else {
                $peers = [
                    'bgpPeerRemoteAs' => $value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'],
                    'astext' => $astext,
                ];
                $affected = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->update($peers);
                $seenPeerID[] = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->select('bgpPeer_id')->orderBy('bgpPeer_id', 'ASC')->first()->bgpPeer_id;
                echo str_repeat('.', $affected);
            }
        }
    }

    // clean up peers
    if (isset($seenPeerID) && ! is_null($seenPeerID)) {
        $deleted = DeviceCache::getPrimary()->bgppeers()->whereNotIn('bgpPeer_id', $seenPeerID)->delete();
        echo str_repeat('-', $deleted);
    }

    unset($bgpPeers);


    // ---- 2️⃣ cbgp (AFI/SAFI) discovery for Nokia ----
    echo ' TIMOS cbgp discovery ';

    $afi_map = [
        1 => 'ipv4',
        2 => 'ipv6',
    ];
    $safi_map = [
        1 => 'unicast',
        2 => 'multicast',
        128 => 'vpn',
    ];

    // Step 1: Gather peer table (for IP lookup)
    $peer_table = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgTable', [], 'TIMETRA-BGP-MIB', 'nokia', '-OQUsb');
    d_echo($peer_table);

    // Step 2: Gather AFI/SAFI combinations
    $afisafi_table = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgAfiSafiTable', [], 'TIMETRA-BGP-MIB', 'nokia', '-OQUsb');
    d_echo($afisafi_table);

    // Step 3: Gather prefix counters (using your confirmed OIDs)
    $prefix_recv = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgOperReceivedPrefixes', [], 'TIMETRA-BGP-MIB', 'nokia', '-OQUsb');
    $prefix_sent = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgOperSentPrefixes', [], 'TIMETRA-BGP-MIB', 'nokia', '-OQUsb');

    foreach ($afisafi_table as $index => $entry) {
        $parts = explode('.', $index);
        if (count($parts) < 3) {
            continue;
        }

        $safi = array_pop($parts);
        $afi = array_pop($parts);
        $peer_index = implode('.', $parts);

        $afi_name = $afi_map[$afi] ?? "afi$afi";
        $safi_name = $safi_map[$safi] ?? "safi$safi";

        if (isset($peer_table[$peer_index])) {
            $peer = $peer_table[$peer_index];

            $pfxRcv = $prefix_recv[$index]['tBgpPeerNgOperReceivedPrefixes'] ?? 0;
            $pfxSent = $prefix_sent[$index]['tBgpPeerNgOperSentPrefixes'] ?? 0;

            d_echo("Adding cbgp for $peer_index ($afi_name/$safi_name): recv=$pfxRcv sent=$pfxSent\n");

            add_cbgp_peer($device, $peer, $afi_name, $safi_name, $pfxRcv, $pfxSent);
        }
    }
    // No return statement here, so standard BGP mib will still be polled after this file is executed.
}
