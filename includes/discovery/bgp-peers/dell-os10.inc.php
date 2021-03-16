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
 * @copyright  2021 LibreNMS Contributors
 * @author     LibreNMS Contributors
 */

use LibreNMS\Config;
use LibreNMS\Util\IP;

if (Config::get('enable_bgp')) {
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

        foreach ($bgpPeers as $vrfInstance => $peer) {
            $vrfId = dbFetchCell('SELECT vrf_id from `vrfs` WHERE vrf_oid = ?', [$vrfInstance]);
            if (is_null($vrfId)) {
                $vrfId = 1; // According to the MIB
            }
            foreach ($peer as $address => $value) {
                // resolve AS number by DNS_TXT record
                $astext = get_astext($value['os10bgp4V2PeerRemoteAs']);

                // FIXME - the `devices` table gets updated in the main bgp-peers.inc.php
                // Setting it here avoids the code that resets it to null if not found in BGP4-MIB.
                $bgpLocalAs = $value['os10bgp4V2PeerLocalAs'];

                if (dbFetchCell('SELECT count(*) FROM `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]) == 0) {
                    $row = [
                        'device_id' => $device['device_id'],
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
                    dbInsert($row, 'bgpPeers');

                    if (Config::get('autodiscovery.bgp')) {
                        $name = gethostbyaddr($address);
                        discover_new_device($name, $device, 'BGP');
                    }
                    echo '+';
                } else {
                    dbUpdate(['bgpPeerRemoteAs' => $value['os10bgp4V2PeerRemoteAs'], 'astext' => $astext], 'bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
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
        if (dbFetchCell('SELECT count(*) FROM `vrfs` WHERE `device_id` = ?', [$device['device_id']]) == 0) {
            $peers = dbFetchRows('SELECT `vrf_id`, `bgpPeerIdentifier` FROM `bgpPeers` WHERE `device_id` = ?', [$device['device_id']]);
        } else {
            $peers = dbFetchRows('SELECT `B`.`vrf_id` AS `vrf_id`, `bgpPeerIdentifier` FROM `bgpPeers` AS B LEFT JOIN `vrfs` AS V ON `B`.`vrf_id` = `V`.`vrf_id` WHERE `B`.`device_id` = ?', [$device['device_id']]);
        }
        foreach ($peers as $peer) {
            $vrfId = $peer['vrf_id'];
            $address = $peer['bgpPeerIdentifier'];

            if (empty($bgpPeers[$vrfInstance][$address])) {
                $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);

                echo str_repeat('-', $deleted);
                echo PHP_EOL;
            }
        }
        unset($bgpPeers);
        // No return statement here, so standard BGP mib will still be polled after this file is executed.
    }
}
