<?php

/**
 * adva_fsp3kr7.inc.php
 *
 * LibreNMS ADVA FSP 3000 R7 port poller include
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Michael Hobl
 * @author     Michael Hobl <michael@hobl.com.au>
 */

// The FSP 3000 R7 has no ifSpeed or ifHighSpeed in IF-MIB, so ports have no speed.
// The facility type of an Ethernet client port gives its rate (in Mbps for ifHighSpeed).
$adva_speeds = [
    'ifType1GbE' => 1000,
    'ifType1000BaseT' => 1000,
    'ifType10GbE' => 10000,
    'ifType10GbELan' => 10000,
    'ifType25GbE' => 25000,
    'ifType40GbE' => 40000,
    'ifType100GbE' => 100000,
];

$adva_facilities = snmpwalk_cache_oid($device, 'entityFacilityType', [], 'ADVA-FSPR7-MIB');

if (! empty($adva_facilities)) {
    // Facilities are indexed by shelf.slot.port.ext.class, the matching ifIndex is entityFacilityOneIndex
    $adva_facilities = snmpwalk_cache_oid($device, 'entityFacilityOneIndex', $adva_facilities, 'ADVA-FSPR7-MIB');

    d_echo($adva_facilities);

    foreach ($adva_facilities as $adva_facility) {
        $adva_ifindex = $adva_facility['entityFacilityOneIndex'] ?? null;
        $adva_speed = $adva_speeds[$adva_facility['entityFacilityType'] ?? ''] ?? null;

        if ($adva_ifindex === null || $adva_speed === null || ! isset($port_stats[$adva_ifindex])) {
            continue;
        }

        if (empty($port_stats[$adva_ifindex]['ifHighSpeed'])) {
            $port_stats[$adva_ifindex]['ifHighSpeed'] = $adva_speed;
        }
    }
}

unset($adva_speeds, $adva_facilities, $adva_facility, $adva_ifindex, $adva_speed);
