<?php

/**
 * vrp.inc.php
 *
 * LibreNMS bgp_peers for Huawei VRP
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
 * @copyright  2020 PipoCanaja
 * @author     PipoCanaja
 */

use LibreNMS\Config;
use LibreNMS\Util\IP;

$bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeerRemoteAs', [], 'HUAWEI-BGP-VPN-MIB');

if (count($bgpPeersCache) == 0) {
    //Either we have no BGP peer, or this VRP device does not support Huawei's own BGP MIB
    //Let's compare with standard BGP4-MIB.
    $bgpPeersCache_ietf = snmpwalk_cache_oid($device, 'bgpPeerRemoteAs', [], 'BGP4-MIB');
}

// So if we have HUAWEI BGP entries or if we don't have anything from HUAWEI nor BGP4-MIB
if (count($bgpPeersCache) > 0 || count($bgpPeersCache_ietf) == 0) {
    $vrfs = DeviceCache::getPrimary()->vrfs()->select('vrf_id', 'vrf_name')->get();
    foreach ($vrfs as $vrf) {
        $map_vrf['byId'][$vrf['vrf_id']]['vrf_name'] = $vrf['vrf_name'];
        $map_vrf['byName'][$vrf['vrf_name']]['vrf_id'] = $vrf['vrf_id'];
    }

    $bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeerAddrFamilyTable', $bgpPeersCache, 'HUAWEI-BGP-VPN-MIB');
    $bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeerTable', $bgpPeersCache, 'HUAWEI-BGP-VPN-MIB');
    $bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeerRouteTable', $bgpPeersCache, 'HUAWEI-BGP-VPN-MIB');
    $bgpPeersCache = snmpwalk_cache_oid($device, 'hwBgpPeerSessionTable', $bgpPeersCache, 'HUAWEI-BGP-VPN-MIB');

    $bgpPeersDesc = snmpwalk_cache_oid($device, 'hwBgpPeerSessionExtDescription', [], 'HUAWEI-BGP-VPN-MIB');

    foreach ($bgpPeersCache as $key => $value) {
        $oid = explode('.', $key);
        $vrfInstance = $value['hwBgpPeerVrfName'];
        if ($oid[0] == 0) {
            $vrfInstance = '';
            $value['hwBgpPeerVrfName'] = '';
        }
        $oid_address = str_replace($oid[0] . '.' . $oid[1] . '.' . $oid[2] . '.' . $oid[3] . '.', '', $key);
        if ($oid[3] == 'ipv4') {
            $address = $oid_address;
        } elseif ($oid[3] == 'ipv6') {
            $address = IP::fromHexString($oid_address)->compressed();
        } else {
            // we have a malformed OID reply, let's skip it
            continue;
        }

        $bgpPeers[$vrfInstance][$address] = $value;
        $bgpPeers[$vrfInstance][$address]['vrf_id'] = $map_vrf['byName'][$vrfInstance]['vrf_id'] ?? null;
        $bgpPeers[$vrfInstance][$address]['afi'] = $oid[1];
        $bgpPeers[$vrfInstance][$address]['safi'] = $oid[2];
        $bgpPeers[$vrfInstance][$address]['typePeer'] = $oid[3];
        if (array_key_exists('0.' . $oid[3] . '.' . $oid_address, $bgpPeersDesc)) {
            // We may have a description
            $bgpPeers[$vrfInstance][$address]['bgpPeerDescr'] = $bgpPeersDesc['0.' . $oid[3] . '.' . $oid_address]['hwBgpPeerSessionExtDescription'];
        }
    }

    foreach ($bgpPeers as $vrfName => $vrf) {
        $vrfId = $map_vrf['byName'][$vrfName]['vrf_id'];

        foreach ($vrf as $address => $value) {
            $astext = \LibreNMS\Util\AutonomousSystem::get($value['hwBgpPeerRemoteAs'])->name();
            if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->exists()) {
                $peers = [
                    'device_id' => $device['device_id'],
                    'vrf_id' => $vrfId,
                    'bgpPeerIdentifier' => $address,
                    'bgpPeerRemoteAs' => $value['hwBgpPeerRemoteAs'] ?? '',
                    'bgpPeerState' => $value['hwBgpPeerState'] ?? '',
                    'bgpPeerAdminStatus' => $value['hwBgpPeerAdminStatus'] ?? '',
                    'bgpLocalAddr' => '0.0.0.0',
                    'bgpPeerRemoteAddr' => $value['hwBgpPeerRemoteAddr'],
                    'bgpPeerInUpdates' => 0,
                    'bgpPeerOutUpdates' => 0,
                    'bgpPeerInTotalMessages' => 0,
                    'bgpPeerOutTotalMessages' => 0,
                    'bgpPeerFsmEstablishedTime' => $value['hwBgpPeerFsmEstablishedTime'],
                    'bgpPeerInUpdateElapsedTime' => 0,
                    'astext' => $astext,
                ];
                if (array_key_exists('bgpPeerDescr', $value)) {
                    $peers['bgpPeerDescr'] = $value['bgpPeerDescr'];
                }
                if (empty($vrfId)) {
                    unset($peers['vrf_id']);
                }
                $seenPeerID[] = DeviceCache::getPrimary()->bgppeers()->create($peers)->bgpPeer_id;

                if (Config::get('autodiscovery.bgp')) {
                    $name = gethostbyaddr($address);
                    discover_new_device($name, $device, 'BGP');
                }
                echo '+';
                $vrp_bgp_peer_count++;
            } else {
                $peers = [
                    'bgpPeerRemoteAs' => $value['hwBgpPeerRemoteAs'],
                    'astext' => $astext,
                ];
                if (array_key_exists('bgpPeerDescr', $value)) {
                    $peers['bgpPeerDescr'] = $value['bgpPeerDescr'];
                }
                $affected = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->update($peers);
                $seenPeerID[] = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->select('bgpPeer_id')->orderBy('bgpPeer_id', 'ASC')->first()->bgpPeer_id;

                echo str_repeat('.', $affected);
                $vrp_bgp_peer_count += $affected;
            }
            if (dbFetchCell('SELECT COUNT(*) from `bgpPeers_cbgp` WHERE device_id = ? AND bgpPeerIdentifier = ? AND afi=? AND safi=?', [$device['device_id'], $value['hwBgpPeerRemoteAddr'], $value['afi'], $value['safi']]) < 1) {
                if ($vrfName != '') {
                    $device['context_name'] = $vrfName;
                }
                add_cbgp_peer($device, ['ip' => $value['hwBgpPeerRemoteAddr']], $value['afi'], $value['safi']);
                unset($device['context_name']);
            } else {
                //nothing to update
            }
        }
    }
    // clean up peers

    if (! is_null($seenPeerID)) {
        $deleted = DeviceCache::getPrimary()->bgppeers()->whereNotIn('bgpPeer_id', $seenPeerID)->delete();
        echo str_repeat('-', $deleted);
    }

    $af_query = 'SELECT bgpPeerIdentifier, afi, safi FROM bgpPeers_cbgp WHERE `device_id`=? AND bgpPeerIdentifier=?';
    foreach (dbFetchRows($af_query, [$device['device_id'], $peer['ip']]) as $entry) {
        $afi = $entry['afi'];
        $safi = $entry['safi'];
        $vrfName = $entry['context_name'];
        if (! isset($bgpPeersCache[$vrfName]) ||
                ! isset($bgpPeersCache[$vrfName][$entry['bgpPeerIdentifier']]) ||
                $bgpPeersCache[$vrfName][$entry['bgpPeerIdentifier']][$entry['afi']] != $afi ||
                $bgpPeersCache[$vrfName][$entry['bgpPeerIdentifier']][$entry['safi']] != $safi) {
            dbDelete(
                'bgpPeers_cbgp',
                '`device_id`=? AND `bgpPeerIdentifier`=? AND context_name=? AND afi=? AND safi=?',
                [$device['device_id'], $address, $vrfName, $afi, $safi]
            );
        }
    }

    unset($bgpPeersCache);
    unset($bgpPeers);
    if ($vrp_bgp_peer_count > 0) {
        return; //Finish BGP discovery here, cause we already collected data with Huawei MIBs
    }
}
// If not, we continue with standard BGP4 MIB
