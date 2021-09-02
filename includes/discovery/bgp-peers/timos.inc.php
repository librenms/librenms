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
 * @copyright  2020 LibreNMS Contributors
 * @author     LibreNMS Contributors
 */

use LibreNMS\Config;
use LibreNMS\Util\IP;

if (Config::get('enable_bgp')) {
    if ($device['os'] == 'timos') {
        $bgpPeersCache = snmpwalk_cache_multi_oid($device, 'tBgpPeerNgTable', [], 'TIMETRA-BGP-MIB', 'nokia');
        foreach ($bgpPeersCache as $key => $value) {
            $oid = explode('.', $key);
            $vrfInstance = $oid[0];
            $address = str_replace($oid[0] . '.' . $oid[1] . '.', '', $key);
            if (strlen($address) > 15) {
                $address = IP::fromHexString($address)->compressed();
            }
            $bgpPeers[$vrfInstance][$address] = $value;
        }
        unset($bgpPeersCache);

        foreach ($bgpPeers as $vrfOid => $vrf) {
            $vrfId = dbFetchCell('SELECT vrf_id from `vrfs` WHERE vrf_oid = ?', [$vrfOid]);
            foreach ($vrf as $address => $value) {
                $astext = get_astext($value['tBgpPeerNgPeerAS4Byte']);

                if (dbFetchCell('SELECT COUNT(*) from `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]) < '1') {
                    $peers = [
                        'device_id' => $device['device_id'],
                        'vrf_id' => $vrfId,
                        'bgpPeerIdentifier' => $address,
                        'bgpPeerRemoteAs' => $value['tBgpPeerNgPeerAS4Byte'],
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
                    dbInsert($peers, 'bgpPeers');
                    if (Config::get('autodiscovery.bgp')) {
                        $name = gethostbyaddr($address);
                        discover_new_device($name, $device, 'BGP');
                    }
                    echo '+';
                } else {
                    dbUpdate(['bgpPeerRemoteAs' => $value['tBgpPeerNgPeerAS4Byte'], 'astext' => $astext], 'bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
                    echo '.';
                }
            }
        }
        // clean up peers
        $peers = dbFetchRows('SELECT `B`.`vrf_id` AS `vrf_id`, `bgpPeerIdentifier`, `vrf_oid` FROM `bgpPeers` AS B LEFT JOIN `vrfs` AS V ON `B`.`vrf_id` = `V`.`vrf_id` WHERE `B`.`device_id` = ?', [$device['device_id']]);
        foreach ($peers as $value) {
            $vrfId = $value['vrf_id'];
            $vrfOid = $value['vrf_oid'];
            $address = $value['bgpPeerIdentifier'];

            if (empty($bgpPeers[$vrfOid][$address])) {
                $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);

                echo str_repeat('-', $deleted);
                echo PHP_EOL;
            }
        }
        unset($bgpPeers);
        // No return statement here, so standard BGP mib will still be polled after this file is executed.
    }
}
