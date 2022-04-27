<?php
/*
 * LibreNMS discovery module for Jetstream IPv4/IPv6 Routes
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

use LibreNMS\Util\IPv4;

$oids = SnmpQuery::walk('TPLINK-STATICROUTE-MIB::tpStaticRouteConfigTable')->table(3);
if (isset($oids)) {
    d_echo('ROUTE: Jetstream IPv4');
    $oids = call_user_func_array('array_merge', $oids);
    $oids = call_user_func_array('array_merge', $oids);

    foreach ($oids as $data) {
        unset($entryClean);
        $entryClean['device_id'] = $device['device_id'];
        $entryClean['inetCidrRouteDestType'] = 'ipv4';
        $entryClean['inetCidrRouteDest'] = $data['TPLINK-STATICROUTE-MIB::tpStaticRouteItemDesIp'];
        $entryClean['inetCidrRoutePfxLen'] = IPv4::netmask2cidr($data['TPLINK-STATICROUTE-MIB::tpStaticRouteItemMask']); //CONVERT
        $entryClean['inetCidrRouteNextHopType'] = 'ipv4';
        $entryClean['inetCidrRouteNextHop'] = $data['TPLINK-STATICROUTE-MIB::tpStaticRouteItemNextIp'];
        $entryClean['inetCidrRouteNextHopAS'] = '0';
        $entryClean['inetCidrRouteProto'] = '3';
        $entryClean['inetCidrRouteType'] = '4';
        $entryClean['context_name'] = '';
        $entryClean['updated_at'] = $update_timestamp;

        // InterfaceName & Distance are swapped on different chipsets
        if (preg_match('/^vlan([\d]+)$/i', $data['TPLINK-STATICROUTE-MIB::tpStaticRouteItemInterfaceName'], $intName)) { //other TP-LINKs
            $metric = $data['tpStaticRouteItemDistance'];
        } else {
            preg_match('/^vlan([\d]+)$/i', $data['TPLINK-STATICROUTE-MIB::tpStaticRouteItemDistance'], $intName); //T1600-g28-v2 Broadcom chipset
            $metric = $data['tpStaticRouteItemInterfaceName'];
        }

        if (! empty($intName)) {
            $ifIndex = $intName[1];
            $entryClean['inetCidrRouteMetric1'] = $metric;
            $entryClean['inetCidrRouteIfIndex'] = $ifIndex;

            $portId = get_port_by_index_cache($device['device_id'], $ifIndex)['port_id'];
            $entryClean['port_id'] = $portId;
            $entryClean['inetCidrRoutePolicy'] = 'zeroDotZero.' . $ifIndex;

            $current = $mixed['']['ipv4'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv4'][$inetCidrRouteNextHop];
            if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['route_id']] != 1) {
                //we already have a row in DB
                $entryClean['route_id'] = $current['db']['route_id'];
                $update_row[] = $entryClean;
            } else {
                $entry['created_at'] = ['NOW()'];
                $create_row[] = $entryClean;
            }
        }
    }
}

$oids = snmpwalk_cache_oid($device, 'TPLINK-IPV6STATICROUTE-MIB::tpIPv6StaticRouteConfigTable', [], 'TPLINK-IPV6STATICROUTE-MIB');
if (isset($oids)) {
    d_echo('ROUTE: Jetstream IPv6');
    foreach ($oids as $data) {
        $ipv6dst = normalize_snmp_ip_address(str_replace(' ', ':', trim($data['tpIPv6StaticRouteItemDesIp'])));
        $ipv6hop = normalize_snmp_ip_address(str_replace(' ', ':', trim($data['tpIPv6StaticRouteItemNexthop'])));
        unset($entryClean);
        $entryClean['device_id'] = $device['device_id'];
        $entryClean['inetCidrRouteDestType'] = 'ipv6';
        $entryClean['inetCidrRouteDest'] = $ipv6dst;
        $entryClean['inetCidrRoutePfxLen'] = $data['tpIPv6StaticRouteItemPrefixLen'];
        $entryClean['inetCidrRouteNextHopType'] = 'ipv6';
        $entryClean['inetCidrRouteNextHop'] = $ipv6hop;
        $entryClean['inetCidrRouteNextHopAS'] = '0';
        $entryClean['inetCidrRouteProto'] = '3';
        $entryClean['inetCidrRouteType'] = '4';
        $entryClean['inetCidrRouteMetric1'] = intval($data['tpIPv6StaticRouteItemDistance']);
        $entryClean['context_name'] = '';
        $entryClean['updated_at'] = $update_timestamp;

        if (preg_match('/\d+/', $data['tpIPv6StaticRouteItemInterfaceName'], $out)) {
            $ifIndex = $out[0];
            $entryClean['inetCidrRouteIfIndex'] = $ifIndex;
            $portId = get_port_by_index_cache($device['device_id'], $ifIndex)['port_id'];
            $entryClean['port_id'] = $portId;
            $entryClean['inetCidrRoutePolicy'] = 'zeroDotZero.' . $ifIndex;

            $current = $mixed['']['ipv4'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv4'][$inetCidrRouteNextHop];
            if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['route_id']] != 1) {
                //we already have a row in DB
                $entryClean['route_id'] = $current['db']['route_id'];
                $update_row[] = $entryClean;
            } else {
                $entry['created_at'] = ['NOW()'];
                $create_row[] = $entryClean;
            }
        }
    }
}

