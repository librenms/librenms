<?php

/**
 * dell-os10.inc.php
 *
 * LibreNMS bgp_peers for Dell OS10
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
 * @copyright  2021 LibreNMS Contributors
 * @author     LibreNMS Contributors
 */

use App\Models\BgpPeer;
use App\Models\Vrf;
use LibreNMS\Config;
use LibreNMS\Util\IP;

if ($device['os'] == 'dell-os10') {
    $bgpPeersCache = snmpwalk_cache_multi_oid($device, 'os10bgp4V2PeerTable', [], 'DELLEMC-OS10-BGP4V2-MIB', 'dell');
    foreach ($bgpPeersCache as $key => $value) {
        $oid = explode('.', $key);
        $vrfInstance = array_shift($oid);       // os10bgp4V2PeerInstance
        $remoteAddressType = array_shift($oid); // os10bgp4V2PeerRemoteAddrType
        $address = IP::fromSnmpString(implode(' ', $oid))->compressed(); // os10bgp4V2PeerRemoteAddr
        $bgpPeers[$vrfInstance][$address] = $value;
    }
    unset($bgpPeersCache);

    $vrfs = DeviceCache::getPrimary()->vrfs->pluck('vrf_id', 'vrf_oid');

    foreach ($bgpPeers as $vrfInstance => $peer) {
        $vrfId = $vrfs->get($vrfInstance, 1); // According to the MIB

        foreach ($peer as $address => $value) {
            // resolve AS number by DNS_TXT record
            $astext = \LibreNMS\Util\AutonomousSystem::get($value['os10bgp4V2PeerRemoteAs'])->name();

            // FIXME - the `devices` table gets updated in the main bgp-peers.inc.php
            // Setting it here avoids the code that resets it to null if not found in BGP4-MIB.
            $bgpLocalAs = $value['os10bgp4V2PeerLocalAs'];

            if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->exists()) {
                $row = [
                    'vrf_id' => $vrfId,
                    'bgpPeerIdentifier' => $address,
                    'bgpPeerRemoteAs' => $value['os10bgp4V2PeerRemoteAs'],
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
                DeviceCache::getPrimary()->bgppeers()->create($row);

                if (Config::get('autodiscovery.bgp')) {
                    $name = gethostbyaddr($address);
                    discover_new_device($name, $device, 'BGP');
                }
                echo '+';
            } else {
                BgpPeer::where('bgpPeerRemoteAs', $value['os10bgp4V2PeerRemoteAs'])->where('astext', $astext)->update(['bgpPeerIdentifier' => $address, 'device_id' => $device['device_id'], 'vrf_id' => $vrfId]);
                echo '.';
            }
        }
    }

    $af_data = snmpwalk_cache_oid($device, 'os10bgp4V2PrefixInPrefixes', [], 'DELLEMC-OS10-BGP4V2-MIB', 'dell');
    foreach ($af_data as $key => $value) {
        $oid = explode('.', $key);
        $vrfInstance = array_shift($oid);       // os10bgp4V2PeerInstance
        $remoteAddressType = array_shift($oid); // os10bgp4V2PeerRemoteAddrType
        $safi = array_pop($oid);                // os10bgp4V2PrefixGaugesSafi
        $afi = array_pop($oid);                 // os10bgp4V2PrefixGaugesAfi
        $address = IP::fromSnmpString(implode(' ', $oid))->compressed(); // os10bgp4V2PeerRemoteAddr
        // add to `bgpPeers_cbgp` table
        add_cbgp_peer($device, ['ip' => $address], $afi, $safi);
    }

    // clean up peers
    if (Vrf::where('device_id', $device['device_id'])->count() == 0) {
        $peers = BgpPeer::select('vrf_id', 'bgpPeerIdentifier')->where('device_id', $device['device_id']);
    } else {
        $peers = dbFetchRows('SELECT `B`.`vrf_id` AS `vrf_id`, `bgpPeerIdentifier` FROM `bgpPeers` AS B LEFT JOIN `vrfs` AS V ON `B`.`vrf_id` = `V`.`vrf_id` WHERE `B`.`device_id` = ?', [$device['device_id']]);
    }
    foreach ($peers as $peer) {
        $vrfId = $peer['vrf_id'];
        $address = $peer['bgpPeerIdentifier'];

        if (empty($bgpPeers[$vrfInstance][$address])) {
            $deleted = BgpPeer::where('device_id', $device['device_id'])->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->delete();

            echo str_repeat('-', $deleted);
            echo PHP_EOL;
        }
    }
    unset($bgpPeers);
    // No return statement here, so standard BGP mib will still be polled after this file is executed.
}
