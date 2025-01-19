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
 * @copyright  2022 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use LibreNMS\Util\IPv6;

$oids = SnmpQuery::walk('IPV6-MIB::ipv6RouteTable')->table(1);

foreach ($oids as $dst => $data) {
    $PfxLen = array_key_first($data['IPV6-MIB::ipv6RouteIfIndex']);
    $RouteIndex = array_key_first($data['IPV6-MIB::ipv6RouteIfIndex'][$PfxLen]);

    //route destination
    $ipv6dst = new IPv6($dst);
    $dst_uncompressed = $ipv6dst->uncompressed();

    //next hop
    $ipv6hop = new IPv6($data['IPV6-MIB::ipv6RouteNextHop'][$PfxLen][$RouteIndex]);
    $hop_uncompressed = $ipv6hop->uncompressed();

    //portId from ifIndex
    $ifIndex = $data['IPV6-MIB::ipv6RouteIfIndex'][$PfxLen][$RouteIndex];
    $portId = \App\Facades\PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

    //populate array with data
    unset($entryClean);
    $entryClean['updated_at'] = $update_timestamp;
    $entryClean['device_id'] = $device['device_id'];
    $entryClean['port_id'] = $portId;
    $entryClean['context_name'] = '';
    $entryClean['inetCidrRouteIfIndex'] = $ifIndex;
    $entryClean['inetCidrRouteType'] = $data['IPV6-MIB::ipv6RouteType'][$PfxLen][$RouteIndex];
    $entryClean['inetCidrRouteProto'] = $data['IPV6-MIB::ipv6RouteProtocol'][$PfxLen][$RouteIndex];
    $entryClean['inetCidrRouteNextHopAS'] = '0';
    $entryClean['inetCidrRouteMetric1'] = $data['IPV6-MIB::ipv6RouteMetric'][$PfxLen][$RouteIndex];
    $entryClean['inetCidrRouteDestType'] = 'ipv6';
    $entryClean['inetCidrRouteDest'] = $dst_uncompressed;
    $entryClean['inetCidrRouteNextHopType'] = 'ipv6';
    $entryClean['inetCidrRouteNextHop'] = $hop_uncompressed;
    $entryClean['inetCidrRouteNextHopType'] = 'ipv6';
    $entryClean['inetCidrRoutePolicy'] = $data['IPV6-MIB::ipv6RoutePolicy'][$PfxLen][$RouteIndex];
    $entryClean['inetCidrRoutePfxLen'] = $PfxLen;

    $current = $mixed['']['ipv6'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv6'][$inetCidrRouteNextHop];
    if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['route_id']] != 1) {
        //we already have a row in DB
        $update_row[] = $entryClean;
    } else {
        $entry['created_at'] = ['NOW()'];
        $create_row[] = $entryClean;
    }
}
