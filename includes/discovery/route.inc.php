<?php
/* Copyright (C) 2014 Nicolas Armando <nicearma@yahoo.com>
 * Copyright (C) 2014 Mathieu Millet <htam-net@github.net>
 * Copyright (C) 2019 PipoCanaja <pipocanaja@github.net>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

//We can use RFC1213 or IP-FORWARD-MIB or MPLS-L3VPN-STD-MIB

use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Util\IPv4;

$ipForwardMibRoutesNumber = snmp_get($device, 'IP-FORWARD-MIB::inetCidrRouteNumber.0', '-Osqn');

$ipForwardNb = snmp_get_multi($device, ['inetCidrRouteNumber.0', 'ipCidrRouteNumber.0'], '-OQUs', 'IP-FORWARD-MIB');

//Get the configured max routes number
$max_routes = 1000;
if (null != (Config::get('routes_max_number'))) {
    $max_routes = Config::get('routes_max_number');
}

//Init update/create tables;
$create_row = [];
$update_row = [];
$delete_row = [];

//store timestamp so all update / creation will be synced on same timestamp
$update_timestamp = dbFetchRows('select now() as now')[0]['now'];

//Load current DB entries:
$dbRoute = dbFetchRows('select * from `route` where `device_id` = ?', [$device['device_id']]);
foreach ($dbRoute as $dbRow) {
    $current = $mixed[$dbRow['context_name']][$dbRow['inetCidrRouteDestType']][$dbRow['inetCidrRouteDest']][$dbRow['inetCidrRoutePfxLen']][$dbRow['inetCidrRoutePolicy']][$dbRow['inetCidrRouteNextHopType']][$dbRow['inetCidrRouteNextHop']];
    if (isset($current) && isset($current['db']) && count($current['db']) > 0) {
        //We have duplicate routes in DB, we'll clean that.
        $delete_row[$dbRow['route_id']] = 1;
        $delete_row_data[$dbRow['route_id']] = $dbRow; //DEBUG DATA ONLY
    } else {
        $mixed[$dbRow['context_name']][$dbRow['inetCidrRouteDestType']][$dbRow['inetCidrRouteDest']][$dbRow['inetCidrRoutePfxLen']][$dbRow['inetCidrRoutePolicy']][$dbRow['inetCidrRouteNextHopType']][$dbRow['inetCidrRouteNextHop']]['db'] = $dbRow;
    }
}

//Not a single route will be discovered if the amount is over maximum
// To prevent any bad behaviour on routers holding the full internet table

//if the device does not support IP-FORWARD-MIB, we can still discover the ipv4 (only)
//routes using RFC1213 but no way to limit the amount of routes here !!

if (! isset($ipForwardNb['0']['inetCidrRouteNumber'])) {
    //RFC1213-MIB
    $mib = 'RFC1213-MIB';
    $tableRoute = [];

    $oid = '.1.3.6.1.2.1.4.21';
    $tableRoute = snmpwalk_group($device, $oid, $mib, 1, []);
    d_echo('Routing table:');
    d_echo($tableRoute);
    echo 'RFC1213 ';
    foreach ($tableRoute as $ipRoute) {
        if (empty($ipRoute['ipRouteDest']) || $ipRoute['ipRouteDest'] == '') {
            continue;
        }

        unset($entryClean);
        $entryClean['inetCidrRouteDestType'] = 'ipv4';
        $entryClean['inetCidrRouteDest'] = $ipRoute['ipRouteDest'];
        $inetCidrRoutePfxLen = IPv4::netmask2cidr($ipRoute['ipRouteMask']); //CONVERT
        $entryClean['inetCidrRoutePfxLen'] = $inetCidrRoutePfxLen;
        $entryClean['inetCidrRoutePolicy'] = $ipRoute['ipRouteInfo'];
        $entryClean['inetCidrRouteNextHopType'] = 'ipv4';
        $entryClean['inetCidrRouteNextHop'] = $ipRoute['ipRouteNextHop'];
        $entryClean['inetCidrRouteMetric1'] = $ipRoute['ipRouteMetric1'];
        $entryClean['inetCidrRouteNextHopAS'] = '0';
        $entryClean['inetCidrRouteProto'] = $ipRoute['ipRouteProto'];
        $entryClean['inetCidrRouteType'] = $ipRoute['ipRouteType'];
        $entryClean['inetCidrRouteIfIndex'] = $ipRoute['ipRouteIfIndex'];
        $entryClean['context_name'] = '';
        $entryClean['device_id'] = $device['device_id'];
        $entryClean['port_id'] = Device::find($device['device_id'])->ports()->where('ifIndex', '=', $entryClean['inetCidrRouteIfIndex'])->first()->port_id;
        $entryClean['updated_at'] = $update_timestamp;
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

// Not a single route will be discovered if the amount is over maximum
// To prevent any bad behaviour on routers holding the full internet table

// IP-FORWARD-MIB with inetCidrRouteTable

if (isset($ipForwardNb['0']['inetCidrRouteNumber']) && $ipForwardNb['0']['inetCidrRouteNumber'] < $max_routes) {
    // We have ip forward mib available
    d_echo('IP FORWARD MIB (with inetCidr support)');
    $mib = 'IP-FORWARD-MIB';
    $oid = '.1.3.6.1.2.1.4.24.7.1';
    $res = snmpwalk_group($device, $oid, $mib, 6, []);
    $ipForwardNb['0']['inetCidrRouteNumber'] = count($res); // Some cisco devices report ipv4+ipv6 but only include ipv6 in this table
    echo 'inetCidrRoute ';
    foreach ($res as $inetCidrRouteDestType => $next1) {
        //ipv4 or ipv6
        foreach ($next1 as $inetCidrRouteDest => $next2) {
            //we have only 1 child here, the mask
            $inetCidrRoutePfxLen = array_keys($next2)[0];
            $next3 = array_values($next2)[0];
            $inetCidrRoutePolicy = array_keys($next3)[0];
            $next4 = array_values($next3)[0];
            foreach ($next4 as $inetCidrRouteNextHopType => $next5) {
                foreach ($next5 as $inetCidrRouteNextHop => $entry) {
                    $entry['inetCidrRouteDestType'] = $inetCidrRouteDestType;
                    $entry['inetCidrRouteDest'] = normalize_snmp_ip_address($inetCidrRouteDest);
                    $entry['inetCidrRoutePfxLen'] = $inetCidrRoutePfxLen;
                    $entry['inetCidrRoutePolicy'] = $inetCidrRoutePolicy;
                    $entry['inetCidrRouteNextHopType'] = $inetCidrRouteNextHopType;
                    $entry['inetCidrRouteNextHop'] = normalize_snmp_ip_address($inetCidrRouteNextHop);
                    $entry['context_name'] = '';
                    $entry['device_id'] = $device['device_id'];
                    $entry['port_id'] = Device::find($device['device_id'])->ports()->where('ifIndex', '=', $entry['inetCidrRouteIfIndex'])->first()->port_id;
                    $entry['updated_at'] = $update_timestamp;
                    unset($entry['inetCidrRouteAge']);
                    unset($entry['inetCidrRouteMetric2']);
                    unset($entry['inetCidrRouteMetric3']);
                    unset($entry['inetCidrRouteMetric4']);
                    unset($entry['inetCidrRouteMetric5']);
                    unset($entry['inetCidrRouteStatus']);
                    $entryPerType[$inetCidrRouteDestType]++;
                    $current = $mixed[''][$inetCidrRouteDestType][$inetCidrRouteDest][$inetCidrRoutePfxLen][$inetCidrRoutePolicy][$inetCidrRouteNextHopType][$inetCidrRouteNextHop];
                    if (! empty($current['db']) && $delete_row[$current['db']['route_id']] != 1) {
                        //we already have a row in DB
                        $entry['route_id'] = $current['db']['route_id'];
                        $update_row[] = $entry;
                    } else {
                        d_echo(isset($current));
                        d_echo(isset($current['db']));
                        d_echo($current['db']);
                        d_echo($delete_row[$current['db']['route_id']]);
                        $entry['created_at'] = ['NOW()'];
                        $create_row[] = $entry;
                    }
                }
            }
        }
    }

    $ipForwardNb['0']['inetCidrRouteNumber'] = $entryPerType['ipv4'];
    // Some cisco devices report ipv4+ipv6 in inetCidrRouteNumber
    // But only include ipv6 in inetCidrRoute
    // So we count the real amount of ipv4 we get, in order to get the missing ipv4 from ipCidrRouteTable if needed
}

// IP-FORWARD-MIB with ipCidrRouteTable in case ipCidrRouteTable has more entries than inetCidrRouteTable (Some older routers)

if (isset($ipForwardNb['0']['ipCidrRouteNumber']) && $ipForwardNb['0']['ipCidrRouteNumber'] > $ipForwardNb['0']['inetCidrRouteNumber'] && $ipForwardNb['0']['ipCidrRouteNumber'] < $max_routes) {
    //device uses only ipCidrRoute and not inetCidrRoute
    d_echo('IP FORWARD MIB (without inetCidr support)');
    $mib = 'IP-FORWARD-MIB';
    $oid = '.1.3.6.1.2.1.4.24.4.1';
    $ipCidrTable = snmpwalk_group($device, $oid, $mib, 6, []);
    echo 'ipCidrRouteTable ';
    // we need to translate the values to inetCidr structure;
    //d_echo($ipCidrTable);
    foreach ($ipCidrTable as $inetCidrRouteDest => $next1) {
        foreach ($next1 as $ipCidrRouteMask => $next2) {
            foreach ($next2 as $ipCidrRouteTos => $next3) {
                foreach ($next3 as $inetCidrRouteNextHop => $entry) {
                    unset($entryClean);
                    $entryClean['inetCidrRouteDestType'] = 'ipv4';
                    $entryClean['inetCidrRouteDest'] = $inetCidrRouteDest;
                    $inetCidrRoutePfxLen = IPv4::netmask2cidr($entry['ipCidrRouteMask']); //CONVERT
                    $entryClean['inetCidrRoutePfxLen'] = $inetCidrRoutePfxLen;
                    $entryClean['inetCidrRoutePolicy'] = $entry['ipCidrRouteInfo'];
                    $entryClean['inetCidrRouteNextHopType'] = 'ipv4';
                    $entryClean['inetCidrRouteNextHop'] = $inetCidrRouteNextHop;
                    $entryClean['inetCidrRouteMetric1'] = $entry['ipCidrRouteMetric1'];
                    $entryClean['inetCidrRouteProto'] = $entry['ipCidrRouteProto'];
                    $entryClean['inetCidrRouteType'] = $entry['ipCidrRouteType'];
                    $entryClean['inetCidrRouteIfIndex'] = $entry['ipCidrRouteIfIndex'];
                    $entryClean['inetCidrRouteNextHopAS'] = $entry['ipCidrRouteNextHopAS'];
                    $entryClean['context_name'] = '';
                    $entryClean['device_id'] = $device['device_id'];
                    $entryClean['port_id'] = Device::find($device['device_id'])->ports()->where('ifIndex', '=', $entryClean['inetCidrRouteIfIndex'])->first()->port_id;
                    $entryClean['updated_at'] = $update_timestamp;
                    $current = $mixed['']['ipv4'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv4'][$inetCidrRouteNextHop];
                    if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['route_id']] != 1) {
                        //we already have a row in DB
                        $entryClean['route_id'] = $current['db']['route_id'];
                        $update_row[] = $entryClean;
                    } else {
                        $entryClean['created_at'] = ['NOW()'];
                        $create_row[] = $entryClean;
                    }
                }
            }
        }
    }
}

// We can now check if we have MPLS VPN routing table available :
// MPLS-L3VPN-STD-MIB::mplsL3VpnVrfRteTable
// Route numbers : MPLS-L3VPN-STD-MIB::mplsL3VpnVrfPerfCurrNumRoutes

$mib = 'MPLS-L3VPN-STD-MIB';
$oid = 'mplsL3VpnVrfPerfCurrNumRoutes';
$mpls_vpn_route_nb = snmpwalk_group($device, $oid, $mib, 6, []);

foreach ($mpls_vpn_route_nb as $vpnId => $route_nb) {
    if ($route_nb['mplsL3VpnVrfPerfCurrNumRoutes'] > $max_routes) {
        echo "Skipping all MPLS routes because vpn instance $vpnId has more than $max_routes routes.";
        $mpls_skip = 1;
    }
}

if ($mpls_skip != 1) {
    echo 'mplsL3VpnVrfRteTable ';
    // We can discover the routes;
    $oid = 'mplsL3VpnVrfRteTable';
    $mpls_route_table = snmpwalk_group($device, $oid, $mib, 7, []);
    foreach ($mpls_route_table as $vpnId => $inetCidrRouteTable) {
        foreach ($inetCidrRouteTable as $inetCidrRouteDestType => $next1) {
            //ipv4 or ipv6
            foreach ($next1 as $inetCidrRouteDest => $next2) {
                //we have only 1 child here, the mask
                $inetCidrRoutePfxLen = array_keys($next2)[0];
                $next3 = array_values($next2)[0];
                $inetCidrRoutePolicy = array_keys($next3)[0];
                $next4 = array_values($next3)[0];
                foreach ($next4 as $inetCidrRouteNextHopType => $next5) {
                    foreach ($next5 as $inetCidrRouteNextHop => $entry) {
                        $entry['inetCidrRouteDestType'] = $inetCidrRouteDestType;
                        $entry['inetCidrRouteDest'] = normalize_snmp_ip_address($inetCidrRouteDest);
                        $entry['inetCidrRoutePfxLen'] = $inetCidrRoutePfxLen;
                        $entry['inetCidrRoutePolicy'] = $inetCidrRoutePolicy;
                        $entry['inetCidrRouteNextHopType'] = $inetCidrRouteNextHopType;
                        $entry['inetCidrRouteNextHop'] = normalize_snmp_ip_address($inetCidrRouteNextHop);
                        $entry['context_name'] = $vpnId;
                        $entry['device_id'] = $device['device_id'];
                        $entry['inetCidrRouteIfIndex'] = $entry['mplsL3VpnVrfRteInetCidrIfIndex'];
                        $entry['port_id'] = Device::find($device['device_id'])->ports()->where('ifIndex', '=', $entry['inetCidrRouteIfIndex'])->first()->port_id;
                        $entry['updated_at'] = $update_timestamp;
                        $entry['inetCidrRouteType'] = $entry['mplsL3VpnVrfRteInetCidrType'];
                        $entry['inetCidrRouteProto'] = $entry['mplsL3VpnVrfRteInetCidrProto'];
                        $entry['inetCidrRouteMetric1'] = $entry['mplsL3VpnVrfRteInetCidrMetric1'];
                        $entry['inetCidrRouteNextHopAS'] = $entry['mplsL3VpnVrfRteInetCidrNextHopAS'];
                        unset($entry['mplsL3VpnVrfRteXCPointer']);
                        unset($entry['mplsL3VpnVrfRteInetCidrMetric1']);
                        unset($entry['mplsL3VpnVrfRteInetCidrMetric2']);
                        unset($entry['mplsL3VpnVrfRteInetCidrMetric3']);
                        unset($entry['mplsL3VpnVrfRteInetCidrMetric4']);
                        unset($entry['mplsL3VpnVrfRteInetCidrMetric5']);
                        unset($entry['mplsL3VpnVrfRteInetCidrAge']);
                        unset($entry['mplsL3VpnVrfRteInetCidrProto']);
                        unset($entry['mplsL3VpnVrfRteInetCidrType']);
                        unset($entry['mplsL3VpnVrfRteInetCidrStatus']);
                        unset($entry['mplsL3VpnVrfRteInetCidrIfIndex']);
                        unset($entry['mplsL3VpnVrfRteInetCidrNextHopAS']);
                        $current = $mixed[$vpnId][$inetCidrRouteDestType][$inetCidrRouteDest][$inetCidrRoutePfxLen][$inetCidrRoutePolicy][$inetCidrRouteNextHopType][$inetCidrRouteNextHop];
                        if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['route_id']] != 1) {
                            //we already have a row in DB
                            $entry['route_id'] = $current['db']['route_id'];
                            $update_row[] = $entry;
                        } else {
                            d_echo(isset($current));
                            d_echo(isset($current['db']));
                            d_echo($current['db']);
                            d_echo(count($current['db']));
                            d_echo($delete_row[$current['db']['route_id']]);
                            $entry['created_at'] = ['NOW()'];
                            $create_row[] = $entry;
                        }
                    }
                }
            }
        }
    }
}
echo "\nProcessing: ";

// We can now process the data into the DB
foreach ($delete_row as $k => $v) {
    if ($v > 0) {
        dbDelete(
            'route',
            '`route_id` = ?',
            [$k]
        );
        echo '-';
        d_echo($delete_row_data[$k]);
    }
}

foreach ($update_row as $upd_entry) {
    dbUpdate(
        $upd_entry,
        'route',
        '`route_id` = ?',
        [$upd_entry['route_id']]
    );
    echo '.';
}

foreach ($create_row as $new_entry) {
    $new_entry['created_at'] = $update_timestamp;
    dbInsert($new_entry, 'route');
    echo '+';
}

// EOF
