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
            $astext = \LibreNMS\Util\AutonomousSystem::get($value['TIMETRA-BGP-MIB::tBgpPeerNgPeerAS4Byte'] ?? null)->name();
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
    $afi_map = [1 => 'ipv4', 2 => 'ipv6'];
    $safi_map = [1 => 'unicast', 2 => 'multicast', 128 => 'vpn'];

    $prefix_oids = [
        '1_1'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.5',  'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.6',  'filter' => 'ipv4'],
        '1_2'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.37', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.38', 'filter' => null],
        '1_128' => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.13', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.14', 'filter' => null],
        '2_1'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.27', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.28', 'filter' => 'ipv6'],
        '2_2'   => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.95', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.96', 'filter' => null],
        '2_128' => ['recv' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.40', 'sent' => '.1.3.6.1.4.1.6527.3.1.2.14.4.8.1.41', 'filter' => null],
    ];

    foreach ($prefix_oids as $oid_key => $oid_set) {
        $recv_data = snmpwalk_cache_oid($device, $oid_set['recv'], [], 'TIMETRA-BGP-MIB');
        $sent_data = snmpwalk_cache_oid($device, $oid_set['sent'], [], 'TIMETRA-BGP-MIB');

        [$afi, $safi] = explode('_', $oid_key);
        $afi_name = $afi_map[(int) $afi] ?? "afi$afi";
        $safi_name = $safi_map[(int) $safi] ?? "safi$safi";

        foreach ($recv_data as $index => $recv_val) {
            $parts = explode('.', (string) $index);
            if (count($parts) < 3) {
                continue;
            }
            $peer_addr_type = $parts[1];
            if ($oid_set['filter'] !== null && $peer_addr_type !== $oid_set['filter']) {
                continue;
            }
            if ($peer_addr_type === 'ipv6') {
                $hex_addr = str_replace(':', '', trim($parts[2], '"'));
                try {
                    $address = IP::fromHexString($hex_addr)->compressed();
                } catch (\LibreNMS\Exceptions\InvalidIpException) {
                    continue;
                }
            } else {
                $address = implode('.', array_slice($parts, 2));
            }
            $peer = ['ip' => $address];
            add_cbgp_peer($device, $peer, $afi_name, $safi_name);
        }
    }

    // No return statement here, so standard BGP mib will still be polled after this file is executed.
}
