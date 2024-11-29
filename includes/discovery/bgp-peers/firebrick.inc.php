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
 *
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

$bgpLocalAs = null;
foreach ($bgpPeers as $vrfId => $vrf) {
    if (empty($vrfId)) {
        $checkVrf = ' AND `vrf_id` IS NULL ';
        // Force to null to avoid 0s going to the DB instead of Nulls
        $vrfId = null;
    } else {
        $vrfs = [
            'vrf_oid' => 'firebrick.' . $vrfId,
            'vrf_name' => $vrfId,
            'device_id' => $device['device_id'],
        ];

        if (! DeviceCache::getPrimary()->vrfs()->where('vrf_oid', $vrfs['vrf_oid'])->exists()) {
            //Should we insert a VRF here ? We are not in the VRF module !
            dbInsert($vrfs, 'vrfs');
        }
    }
    foreach ($vrf as $address => $value) {
        $bgpLocalAs = $value['fbBgpPeerLocalAS'] ?? $bgpLocalAs;
        $astext = \LibreNMS\Util\AutonomousSystem::get($value['fbBgpPeerRemoteAS'])->name();
        if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->exists()) {
            $peers = [
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

            DeviceCache::getPrimary()->bgppeers()->create($peers);

            if (Config::get('autodiscovery.bgp')) {
                $name = gethostbyaddr($address);
                discover_new_device($name, $device, 'BGP');
            }
            echo '+';
        } else {
            $peers = [
                'bgpPeerRemoteAs' => $value['fbBgpPeerRemoteAS'],
                'astext' => $astext,
            ];
            DeviceCache::getPrimary()->bgppeers()->update([
                'bgpPeerIdentifier' => $address,
                'vrf_id' => $vrfId,
            ],
                $peers);
            echo '.';
        }
    }
}
// clean up peers
$peers = DeviceCache::getPrimary()->bgppeers()->select('vrf_id', 'bgpPeerIdentifier');
foreach ($peers as $value) {
    $vrfId = $value['vrf_id'];
    $address = $value['bgpPeerIdentifier'];

    // Cleanup code to deal with 0 vs Null in the DB
    if ($vrfId === 0) {
        // Database says it's table 0 - which is wrong.  It should be "null" for global table
        $deleted = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->delete();
        echo str_repeat('-', $deleted);
        continue;
    } else {
        $testVrfId = empty($vrfId) ? 0 : $vrfId;
    }

    if (empty($bgpPeers[$testVrfId][$address])) {
        $deleted = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $address)->where('vrf_id', $vrfId)->delete();
        echo str_repeat('-', $deleted);
    }
}
