<?php

/*
 * LibreNMS discovery module for RouterOS IPv6 Routes introduced in ROSv7
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use App\Facades\PortCache;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IPv6;

$oids = SnmpQuery::hideMib()->walk('IPV6-MIB::ipv6RouteTable')->table(3);

foreach ($oids as $dst => $tdata) {
    //skip invalid LL routes
    if ($dst == 'fe80:0:0:0:0:0:0:0') {
        continue;
    }

    $pfxLen = key($tdata);
    $tdata = array_shift($tdata);

    foreach ($tdata as $timestamp => $data) {
        try {
            //route destination
            $ipv6dst = IPv6::fromHexString($dst);
            $dst_uncompressed = $ipv6dst->uncompressed();

            //next hop
            $ipv6hop = IPv6::fromHexString($data['ipv6RouteNextHop']);
            $hop_uncompressed = $ipv6hop->uncompressed();

            //portId from ifIndex
            $ifIndex = $data['ipv6RouteIfIndex'];
            $portId = PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

            //populate array with data
            $entryClean = [
                'updated_at' => $update_timestamp,
                'device_id' => $device['device_id'],
                'port_id' => $portId,
                'context_name' => '',
                'inetCidrRouteIfIndex' => $ifIndex,
                'inetCidrRouteType' => $data['ipv6RouteType'] ?? 0,
                'inetCidrRouteProto' => $data['ipv6RouteProtocol'] ?? 0,
                'inetCidrRouteNextHopAS' => '0',
                'inetCidrRouteMetric1' => $data['ipv6RouteMetric'] ?? 0,
                'inetCidrRouteDestType' => 'ipv6',
                'inetCidrRouteDest' => $dst_uncompressed,
                'inetCidrRouteNextHopType' => 'ipv6',
                'inetCidrRouteNextHop' => $hop_uncompressed,
                'inetCidrRoutePolicy' => $data['ipv6RoutePolicy'] ?? 0,
                'inetCidrRoutePfxLen' => $pfxLen,
            ];

            $current = $mixed['']['ipv6'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv6'][$inetCidrRouteNextHop];
            if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['route_id']] != 1) {
                //we already have a row in DB
                $update_row[] = $entryClean;
            } else {
                $entry['created_at'] = ['NOW()'];
                $create_row[] = $entryClean;
            }
        } catch (InvalidIpException $e) {
            Log::error('Failed to parse IP: ' . $e->getMessage());
        }
    }
}
