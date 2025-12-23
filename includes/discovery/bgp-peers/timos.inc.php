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
    $mib_root = '.1.3.6.1.4.1.6527.3.1.2.14.4.8';
    $bgpPeersCache = SnmpQuery::numericIndex()->walk($mib_root)->valuesByIndex();
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
            $astext = \LibreNMS\Util\AutonomousSystem::get($value[$mib_root . '.1.18'] ?? $value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'] ?? null)->name();
            if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->exists()) {
                $peers = [
                    'device_id' => $device['device_id'],
                    'vrf_id' => $vrfId,
                    'bgpPeerIdentifier' => $address,
                    'bgpPeerRemoteAs' => $value[$mib_root . '.1.18'] ?? $value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'] ?? null,
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
                    'bgpPeerRemoteAs' => $value[$mib_root . '.1.18'] ?? $value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'] ?? null,
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
    $afi_map = [
        1 => 'ipv4',
        2 => 'ipv6',
    ];
    $safi_map = [
        1 => 'unicast',
        2 => 'multicast',
        128 => 'vpn',
    ];

    $peer_table_oid = '.1.3.6.1.4.1.6527.3.1.2.14.4.8';
    $afisafi_table_oid = '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.24';

    // Step 1: Gather peer table (for IP lookup)
    $peer_table = snmpwalk_cache_multi_oid($device, $peer_table_oid, [], '', 'nokia', '-OQUsb');
    d_echo($peer_table);

    // Step 2: Gather AFI/SAFI combinations
    $afisafi_table = snmpwalk_cache_multi_oid($device, $afisafi_table_oid, [], '', 'nokia', '-OQUsb');
    d_echo($afisafi_table);

    // 1. Define the OID mapping
    $prefix_oids = [
        '1_1'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.5',  'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.6'],   // IPv4 Unicast
        '1_2'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.37', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.38'],  // IPv4 Multicast
        '1_128' => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.13', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.14'],  // IPv4 VPN

        '2_1'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.27', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.28'], // IPv6 Unicast
        '2_2'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.95', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.96'],  // IPv6 Multicast
        '2_128' => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.40', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.41'],  // IPv6 VPN
    ];

    // 2. Poll ALL the required Nokia OIDs once for efficiency
    $nokia_prefix_data = [];
    foreach ($prefix_oids as $oid_set) {
        // Poll the Received OID
        $data_recv = snmpwalk_cache_multi_oid($device, $oid_set['recv'], [], '', 'nokia', '-OQUsb');
        $nokia_prefix_data[$oid_set['recv']] = $data_recv;

        // Poll the Sent OID
        $data_sent = snmpwalk_cache_multi_oid($device, $oid_set['sent'], [], '', 'nokia', '-OQUsb');
        $nokia_prefix_data[$oid_set['sent']] = $data_sent;
    }
    d_echo($nokia_prefix_data);

    // Step 3: Process AFI/SAFI and use the new OID map
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

        // Key to look up the correct OIDs
        $oid_key = $afi . '_' . $safi;

        if (isset($peer_table[$peer_index]) && isset($prefix_oids[$oid_key])) {
            $peer = $peer_table[$peer_index];
            $oids = $prefix_oids[$oid_key];

            $pfxRcv = $nokia_prefix_data[$oids['recv']][$index][$oids['recv']] ?? 0;
            $pfxSent = $nokia_prefix_data[$oids['sent']][$index][$oids['sent']] ?? 0;

            d_echo("Adding cbgp for $peer_index ($afi_name/$safi_name): recv=$pfxRcv sent=$pfxSent\n");

            add_cbgp_peer($device, $peer, $afi_name, $safi_name, $pfxRcv, $pfxSent);
        }
    }
}
