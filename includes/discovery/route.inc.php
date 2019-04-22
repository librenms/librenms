<?php

/* Copyright (C) 2014 Nicolas Armando <nicearma@yahoo.com> and Mathieu Millet <htam-net@github.net>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>. */


//We only process the global routing table here.
//We can use RFC1213 or IP-FORWARD-MIB

use App\Models\Device;
use LibreNMS\Util\IPv4;

$ipForwardMibRoutesNumber = snmp_get($device, 'IP-FORWARD-MIB::inetCidrRouteNumber.0', '-Osqn');

$data = snmp_get_multi($device, ['inetCidrRouteNumber.0', 'ipCidrRouteNumber.0'], '-OQUs', 'IP-FORWARD-MIB');

d_echo ($data);

//Get the configured max routes number
$max_routes = 10000;
if (null != (Config::get('routes.max_number'))) {
    $max_routes = Config::get('routes.max_number');
}

//Init update/create tables;
$create_row = [];
$update_row = [];
$delete_row = [];

//Load current DB entries: 
$dbRoute = dbFetchRows('select * from `inetCidrRoute` where `device_id` = ? AND `context_name` = ?', array($device['device_id'], ''));
foreach ($dbRoute as $dbRow) {
    $current = $mixed[$dbRow['inetCidrRouteDestType']][$dbRow['inetCidrRouteDest']][$dbRow['inetCidrRoutePfxLen']][$dbRow['inetCidrRoutePolicy']][$dbRow['inetCidrRouteNextHopType']][$dbRow['inetCidrRouteNextHop']];
    if (isset($current) && isset($current['db']) && count($current['db']) > 0) {
        //We have duplicate routes in DB, we'll clean that.
        $delete_row[$dbRow['inetCidrRoute_id']] = 1;
        $delete_row_data[$dbRow['inetCidrRoute_id']] = $dbRow;
        d_echo($dbRow);
    } else {
        $mixed[$dbRow['inetCidrRouteDestType']][$dbRow['inetCidrRouteDest']][$dbRow['inetCidrRoutePfxLen']][$dbRow['inetCidrRoutePolicy']][$dbRow['inetCidrRouteNextHopType']][$dbRow['inetCidrRouteNextHop']]['db'] = $dbRow;
    }
}

//Not a single route will be discovered if the amount is over maximum
// To prevent any bad behaviour on routers holding the full internet table

//if the device does not support IP-FORWARD-MIB, we can still discover the ipv4 (only)
//routes using RFC1213 but no way to limit the amount of routes here !!

if (! isset($data['0']['inetCidrRouteNumber'])) {

    //RFC1213-MIB
    $mib = "RFC1213-MIB";
    $tableRoute = array();

    $oid = '.1.3.6.1.2.1.4.21';
    $ipRoute = snmpwalk_group($device, $oid, $mib, 1, []);
    d_echo($res);
    d_echo('Table routage');
    d_echo($ipRoute);

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
        $entryClean['updated_at'] = array('NOW()');
        $entryClean['inetCidrRouteNextHop_device_id'] = $device['device_id'];
        if ($entryClean['inetCidrRouteNextHop'] != '127.0.0.1' && $entryClean['inetCidrRouteNextHop'] != 'fe80::') {
            $entryClean['inetCidrRouteNextHop_device_id'] = Device::findByIp( $entryClean['inetCidrRouteNextHop'])->device_id;
        };
        $current = $mixed['ipv4'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv4'][$inetCidrRouteNextHop];
        if (isset($current) && isset($current['db']) && count($current['db']) > 0 &&  $delete_row[$current['db']['inetCidrRoute_id']] != 1) {
            //we already have a row in DB
            $update_row[] = $entryClean;
        } else {
            $entry['created_at'] = array('NOW()');
            $create_row[] = $entryClean;
        }

    }
}

// Not a single route will be discovered if the amount is over maximum
// To prevent any bad behaviour on routers holding the full internet table
// IP-FORWARD-MIB with ipCidrRouteTable in case ipCidrRouteTable has more entries than inetCidrRouteTable (Some older routers)

if (isset($data['0']['ipCidrRouteNumber']) && isset($data['0']['inetCidrRouteNumber']) && $data['0']['ipCidrRouteNumber'] > $data['0']['inetCidrRouteNumber'] && $data['0']['ipCidrRouteNumber'] < $max_routes) {
    //device uses only ipCidrRoute and not inetCidrRoute
    d_echo('IP FORWARD MIB (without inetCidr support)');
    $mib = 'IP-FORWARD-MIB';
    $oid = '.1.3.6.1.2.1.4.24.4.1';
    $ipCidrTable = snmpwalk_group($device, $oid, $mib, 6, []);
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
                    $entryClean['updated_at'] = array('NOW()');
                    $entryClean['inetCidrRouteNextHop_device_id'] = $device['device_id'];
                    if ($entryClean['inetCidrRouteNextHop'] != '127.0.0.1' && $entryClean['inetCidrRouteNextHop'] != 'fe80::') {
                        $entryClean['inetCidrRouteNextHop_device_id'] = Device::findByIp( $entryClean['inetCidrRouteNextHop'])->device_id;
                    };

                    $current = $mixed['ipv4'][$inetCidrRouteDest][$inetCidrRoutePfxLen][$entryClean['inetCidrRoutePolicy']]['ipv4'][$inetCidrRouteNextHop];
                    if (isset($current) && isset($current['db']) && count($current['db']) > 0 &&  $delete_row[$current['db']['inetCidrRoute_id']] != 1) {
                        //we already have a row in DB
                        $update_row[] = $entryClean;
                    } else {
                        $entry['created_at'] = array('NOW()');
                        $create_row[] = $entryClean;
                    }
                }
            }
        }
    }
    d_echo('UPDATE:');
    //d_echo($update_row);
    d_echo('CREATE:');
    //d_echo($create_row);
}

// IP-FORWARD-MIB with inetCidrRouteTable

if (isset($data['0']['inetCidrRouteNumber']) && $data['0']['inetCidrRouteNumber'] < $max_routes) {
    // We have ip forward mib available
    d_echo('IP FORWARD MIB (with inetCidr support)');
    $mib = 'IP-FORWARD-MIB';
    $oid = '.1.3.6.1.2.1.4.24.7.1';
    $res = snmpwalk_group($device, $oid, $mib, 6, []);
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
                    $entry['inetCidrRouteDest'] = $inetCidrRouteDest;
                    $entry['inetCidrRoutePfxLen'] = $inetCidrRoutePfxLen;
                    $entry['inetCidrRoutePolicy'] = $inetCidrRoutePolicy;
                    $entry['inetCidrRouteNextHopType'] = $inetCidrRouteNextHopType;
                    $entry['inetCidrRouteNextHop'] = $inetCidrRouteNextHop;
                    $entry['context_name'] = '';
                    $entry['device_id'] = $device['device_id'];
                    $entry['updated_at'] = array('NOW()');
                    $entry['inetCidrRouteNextHop_device_id'] = $device['device_id'];
                    if ($entry['inetCidrRouteNextHop'] != '127.0.0.1') {
                        $entry['inetCidrRouteNextHop_device_id'] = Device::findByIp( $entry['inetCidrRouteNextHop'])->device_id;
                    };
                    unset($entry['inetCidrRouteAge']);
                    unset($entry['inetCidrRouteMetric2']);
                    unset($entry['inetCidrRouteMetric3']);
                    unset($entry['inetCidrRouteMetric4']);
                    unset($entry['inetCidrRouteMetric5']);
                    unset($entry['inetCidrRouteStatus']);
                    $current = $mixed[$inetCidrRouteDestType][$inetCidrRouteDest][$inetCidrRoutePfxLen][$inetCidrRoutePolicy][$inetCidrRouteNextHopType][$inetCidrRouteNextHop];
                    if (isset($current) && isset($current['db']) && count($current['db']) > 0 && $delete_row[$current['db']['inetCidrRoute_id']] != 1) {
                        //we already have a row in DB
                        $update_row[] = $entry;
                    } else {
                        d_echo ( isset($current) );
                         d_echo ( isset($current['db']));
                         d_echo ( $current['db']);
                        d_echo (count($current['db']));
                        d_echo ($delete_row[$current['db']['inetCidrRoute_id']]);
                        $entry['created_at'] = array('NOW()');
                        $create_row[] = $entry;
                    }
                }
            }
        }
    }
}

// We can now process the data into the DB

d_echo('Delete');
d_echo($delete_row);
foreach ($delete_row as $k => $v) {
    if ($v > 0) {
        dbDelete(
            'inetCidrRoute',
            '`inetCidrRoute_id` = ?',
            array($k)
            );
    echo '-';
    d_echo($delete_row_data[$k]);
    }
}

d_echo('Update');
//d_echo($update_row);
foreach ($update_row as $upd_entry) {
    dbUpdate(
            $upd_entry,
            'inetCidrRoute',
            '`inetCidrRoute_id` = ?',
            array($upd_entry['inetCidrRoute_id'])
            );
    echo '.';
}
d_echo('Create');
d_echo($create_row);
foreach ($create_row as $new_entry) {
    dbInsert($new_entry, 'inetCidrRoute');
    echo '+';
}
