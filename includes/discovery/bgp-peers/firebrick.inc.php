<?php
/**
 * firebrick.inc.php
 *
 * LibreNMS bgp_peers for Firebrick 2700/2900/6000
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
 * @copyright  2020 Chris Malton (@cjsoftuk)
 * @author     Chris Malton (@cjsoftuk)
 */

use LibreNMS\Config;
use LibreNMS\Util\IP;

$bgpPeersCache = snmpwalk_cache_multi_oid($device, 'fbBgpPeerTable', [], 'FIREBRICK-BGP-MIB', 'firebrick');
foreach ($bgpPeersCache as $key => $value) {
    $oid = explode('.', $key);
    $protocol = $oid[0];
    $address = str_replace($oid[0] . '.', '', $key);
    if (strlen($address) > 15) {
        $address = IP::fromHexString($address)->compressed();
    }
    if (isset($value['fbBgpPeerTableId']) && $value['fbBgpPeerTableId'] !== '') {
        $bgpPeers[$value['fbBgpPeerTableId']][$address] = $value;
    } else {
        $bgpPeers[0][$address] = $value;
    }
}
unset($bgpPeersCache);

foreach ($bgpPeers as $vrfId => $vrf) {
    if (empty($vrfId)) {
        $checkVrf = ' AND `vrf_id` IS NULL ';
        // Force to null to avoid 0s going to the DB instead of Nulls
        $vrfId = null;
    } else {
        $checkVrf = ' AND vrf_id = ? ';
        $vrfs = [
            'vrf_oid' => 'firebrick.' . $vrfId,
            'vrf_name' => $vrfId,
            'device_id' => $device['device_id'],
        ];

        if (! dbFetchCell('SELECT COUNT(*) FROM vrfs WHERE device_id = ? AND `vrf_oid`=?', [$device['device_id'], $vrfs['vrf_oid']])) {
            dbInsert($vrfs, 'vrfs');
        }
    }
    foreach ($vrf as $address => $value) {
        $astext = get_astext($value['fbBgpPeerRemoteAS']);
        if (dbFetchCell('SELECT COUNT(*) from `bgpPeers` WHERE device_id = ? AND bgpPeerIdentifier = ? ' . $checkVrf, [$device['device_id'], $address, $vrfId]) < '1') {
            $peers = [
                'device_id' => $device['device_id'],
                'vrf_id' => $vrfId,
                'bgpPeerIdentifier' => $address,
                'bgpPeerRemoteAs' => $value['fbBgpPeerRemoteAS'],
                'bgpPeerState' => 'idle',
                'bgpPeerAdminStatus' => 'stop',
                'bgpLocalAddr' => $value['fbBgpPeerLocalAddress'],
                'bgpPeerRemoteAddr' => $value['fbBgpPeerAddress'],
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
            dbUpdate(['bgpPeerRemoteAs' => $value['fbBgpPeerRemoteAS'], 'astext' => $astext], 'bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
            echo '.';
        }
    }
}
// clean up peers
$peers = dbFetchRows('SELECT `vrf_id`, `bgpPeerIdentifier` FROM `bgpPeers` WHERE `device_id` = ?', [$device['device_id']]);
foreach ($peers as $value) {
    $vrfId = $value['vrf_id'];
    $address = $value['bgpPeerIdentifier'];

    // Cleanup code to deal with 0 vs Null in the DB
    if ($vrfId === 0) {
        // Database says it's table 0 - which is wrong.  It should be "null" for global table
        $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
        echo str_repeat('-', $deleted);
        continue;
    } else {
        $testVrfId = empty($vrfId) ? 0 : $vrfId;
    }

    if (empty($bgpPeers[$testVrfId][$address])) {
        if ($vrfId === null) {
            $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id IS NULL', [$device['device_id'], $address]);
        } else {
            $deleted = dbDelete('bgpPeers', 'device_id = ? AND bgpPeerIdentifier = ? AND vrf_id = ?', [$device['device_id'], $address, $vrfId]);
        }
        echo str_repeat('-', $deleted);
    }
}

// TODO: Fix me to use the local AS as published
$bgpLocalAs = $bgpPeers[0][array_keys($bgpPeers[0])[0]]['fbBgpPeerRemoteAS'];
