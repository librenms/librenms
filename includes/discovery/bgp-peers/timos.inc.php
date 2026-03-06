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
            try {
                $address = IP::fromSnmpString($address)->compressed();
            } catch (\LibreNMS\Exceptions\InvalidIpException) {
                d_echo("Nokia TIMOS: Skipping non-IP entry: $address\n");
                continue;
            }
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

    // Remove peers from the database that no longer exist on the router
    if (isset($seenPeerID) && ! is_null($seenPeerID)) {
        $deleted = DeviceCache::getPrimary()->bgppeers()->whereNotIn('bgpPeer_id', $seenPeerID)->delete();
        echo str_repeat('-', $deleted);
    }

    unset($bgpPeers);

    // AFI = Address Family Identifier (what type of IP address)
    $afi_map = [
        1 => 'ipv4',
        2 => 'ipv6',
    ];

    // SAFI = Sub Address Family Identifier (what type of route)
    $safi_map = [
        1   => 'unicast',
        2   => 'multicast',
        128 => 'vpn',
    ];

    // SnmpQuery::numericIndex()->valuesByIndex() returns indexes like: "1.ipv4.62.40.116.67"
    // parts[0]=vrfOid, parts[1]="ipv4"/"ipv6" (string), parts[2..]=address octets
    $prefix_oids = [
        '1_1' => [
            'recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.5',
            'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.6',
            'filter' => 'ipv4',    // Only accept entries where peer is IPv4
        ],
        '1_2' => [
            'recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.37',
            'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.38',
            'filter' => null,
        ],
        '1_128' => [
            'recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.13',
            'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.14',
            'filter' => null,
        ],
        '2_1' => [
            'recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.27',
            'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.28',
            'filter' => 'ipv6',    // Only accept entries where peer is IPv6
        ],
        '2_2' => [
            'recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.95',
            'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.96',
            'filter' => null,
        ],
        '2_128' => [
            'recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.40',
            'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.41',
            'filter' => null,
        ],
    ];

    foreach ($prefix_oids as $oid_key => $oid_set) {

        // Fetch received and sent prefix counts for this AFI/SAFI
        $recv_data = SnmpQuery::numericIndex()->walk($oid_set['recv'])->valuesByIndex();
        $sent_data = SnmpQuery::numericIndex()->walk($oid_set['sent'])->valuesByIndex();

        d_echo("Nokia TIMOS: Processing OID key=$oid_key, entries=" . count($recv_data) . "\n");

        foreach ($recv_data as $index => $recv_entry) {

            $parts = explode('.', (string) $index);

            if (count($parts) < 4) {
                d_echo("Nokia TIMOS: Skipping malformed index: $index\n");
                continue;
            }

            $peer_addr_type = $parts[1];

            if ($oid_set['filter'] !== null && $peer_addr_type !== $oid_set['filter']) {
                d_echo("Nokia TIMOS: Skipping index=$index (peer addr_type=$peer_addr_type, expected={$oid_set['filter']}) — Nokia firmware bug row, ignoring\n");
                continue;
            }

            // Parse the peer IP address directly from the OID index
            $address = implode('.', array_slice($parts, 2));
            if (strlen($address) > 15) {
                try {
                    $address = IP::fromSnmpString($address)->compressed();
                } catch (\LibreNMS\Exceptions\InvalidIpException) {
                    d_echo("Nokia TIMOS: Skipping non-IP entry in prefix walk: $address\n");
                    continue;
                }
            }

            // Build the peer array that add_cbgp_peer() needs
            $peer = ['ip' => $address];

            $pfxRcv  = is_array($recv_entry) ? reset($recv_entry) : ($recv_entry ?? 0);
            $pfxSent = isset($sent_data[$index])
                ? (is_array($sent_data[$index]) ? reset($sent_data[$index]) : $sent_data[$index])
                : 0;

            // Decode oid_key (e.g. "1_1") back to human-readable names
            [$afi, $safi] = explode('_', $oid_key);
            $afi_name  = $afi_map[(int) $afi]   ?? "afi$afi";
            $safi_name = $safi_map[(int) $safi] ?? "safi$safi";

            d_echo("Nokia TIMOS: Writing — index=$index addr=$address addr_type=$peer_addr_type ({$afi_name}/{$safi_name}) recv=$pfxRcv sent=$pfxSent\n");

            add_cbgp_peer($device, $peer, $afi_name, $safi_name);
        }
    }
}
