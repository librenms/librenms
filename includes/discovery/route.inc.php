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


//This file is a litle diferent, because the route depend of the vrf, not of the context, 
//like the others, so if i use the context, you will have the same information n time the context how have the same VRF
global $debug;


$ids = array();

// For the moment only will be cisco and the version 3
if ($device['os_group'] == "cisco") {
    //RFC1213-MIB
    $mib = "RFC1213-MIB";
    //IpRouteEntry
    $vrfs_lite_cisco = array();

    if (key_exists('vrf_lite_cisco', $device) && (count($device['vrf_lite_cisco']) != 0)) {
        //i will only get one context of vrf, read the begin of this file
        foreach ($device['vrf_lite_cisco'] as $vrf_lite) {
            if (!key_exists($vrf_lite['vrf_name'], $vrfs_lite_cisco)) {
                $vrfs_lite_cisco[$vrf_lite['vrf_name']] = $vrf_lite;
            }
        }
    } else {
        $vrfs_lite_cisco = array(array('context_name' => null));
    }

    $tableRoute = array();

    foreach ($vrfs_lite_cisco as $vrf_lite) {
        $device['context_name'] = $vrf_lite['context_name'];

        ////////////////ipRouteDest//////////////////
        $oid = '.1.3.6.1.2.1.4.21.1.1';
        $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);

        foreach (explode("\n", $resultHelp) as $ipRouteDest) {
            list($ip, $value) = explode(" ", $ipRouteDest);
            $tableRoute[$ip]['ipRouteDest'] = $value;
        }

        /////////////////ipRouteIfIndex//////////////
        $oid = '.1.3.6.1.2.1.4.21.1.2';
        $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);

        foreach (explode("\n", $resultHelp) as $ipRouteIfIndex) {
            list($ip, $value) = explode(" ", $ipRouteIfIndex);
            $tableRoute[$ip]['ipRouteIfIndex'] = $value;
        }

        ///////////////ipRouteMetric1///////////////
        $oid = '.1.3.6.1.2.1.4.21.1.3';
        $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);

        foreach (explode("\n", $resultHelp) as $ipRouteMetric) {
            list($ip, $value) = explode(" ", $ipRouteMetric);
            $tableRoute[$ip]['ipRouteMetric'] = $value;
        }

        ////////////ipRouteNextHop//////////////////
        $oid = '.1.3.6.1.2.1.4.21.1.7';
        $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);
        foreach (explode("\n", $resultHelp) as $ipRouteNextHop) {
            list($ip, $value) = explode(" ", $ipRouteNextHop);
            $tableRoute[$ip]['ipRouteNextHop'] = $value;
        }

        ////////////ipRouteType/////////////////////
        $oid = '.1.3.6.1.2.1.4.21.1.8';
        $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);

        foreach (explode("\n", $resultHelp) as $ipRouteType) {
            list($ip, $value) = explode(" ", $ipRouteType);
            $tableRoute[$ip]['ipRouteType'] = $value;
        }

        ///////////ipRouteProto//////////////////////
        $oid = '.1.3.6.1.2.1.4.21.1.9';
        $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);


        foreach (explode("\n", $resultHelp) as $ipRouteProto) {
            list($ip, $value) = explode(" ", $ipRouteProto);
            $tableRoute[$ip]['ipRouteProto'] = $value;
        }

        /*
          ///////////ipRouteAge//////////////////////
          $oid = '.1.3.6.1.2.1.4.21.1.10';
          $resultHelp = snmp_walk($device, $oid, "-Osqn", $mib, NULL);
          $resultHelp = str_replace("$oid.", "", $resultHelp);

          foreach (explode("\n", $resultHelp) as $ipRouteAge) {
              list($ip,$value)=explode(" ",$ipRouteAge);
              $tableRoute[$ip]['ipRouteAge']=$value;
          } */

        ///////////ipRouteMask//////////////////////
        $oid = '.1.3.6.1.2.1.4.21.1.11';
        $resultHelp = snmp_walk($device, $oid, ['-Osq', '-Ln'], $mib, null);
        $resultHelp = trim($resultHelp);
        $resultHelp = str_replace("$oid.", "", $resultHelp);

        foreach (explode("\n", $resultHelp) as $ipRouteMask) {
            list($ip, $value) = explode(" ", $ipRouteMask);
            $tableRoute[$ip]['ipRouteMask'] = $value;
        }

        if ($debug) {
            echo 'Table routage';
            var_dump($tableRoute);
        }

        foreach ($tableRoute as $ipRoute) {
            if (empty($ipRoute['ipRouteDest']) || $ipRoute['ipRouteDest'] == '') {
                continue;
            }

            $oldRouteRow = dbFetchRow('select * from route where device_id = ? AND ipRouteDest = ? AND context_name = ?', array($device['device_id'], $ipRoute['ipRouteDest'], $device['context_name']));
            if (!empty($oldRouteRow)) {
                unset($oldRouteRow['discoveredAt']);
                $changeRoute = array();
                foreach ($ipRoute as $key => $value) {
                    if ($oldRouteRow[$key] != $value) {
                        $changeRoute[$key] = $value;
                    }
                }
                if (!empty($changeRoute)) {
                    dbUpdate($changeRoute, 'route', 'device_id = ? and ipRouteDest = ? and context_name = ?', array($device['device_id'], $ipRoute['ipRouteDest'], $device['context_name']));
                }
            } else {
                $toInsert = array_merge($ipRoute, array('device_id' => $device['device_id'], 'context_name' => $device['context_name'], 'discoveredAt' => time()));
                dbInsert($toInsert, 'route');
            }
        }
        // unset($tableRoute);
    }

    unset($vrfs_lite_cisco);
}
